<?php

namespace TSSGGSModule\Server\app\Client\Parts;

use TSSGGSModule\Server\app\models\whmcs\Client;
use TSSGGSModule\Server\app\models\whmcs\Order;
use TSSGGSModule\Server\app\models\whmcs\Product;
use TSSGGSModule\Server\app\models\whmcs\SSL;
use TSSGGSModule\Server\app\models\whmcs\Service;
use TSSGGSModule\Server\app\repository\whmcs\Config;
use TSSGGSModule\Server\app\repository\whmcs\Domain;
use TSSGGSModule\Server\app\repository\whmcs\ProductConfigOptions;
use TSSGGSModule\Server\Text;
use TSSGGSModule\App\Libs\AutoInstall\AutoInstallHelpers;
use TSSGGSModule\App\Libs\AutoInstall\Cpanel;
use TSSGGSModule\App\Libs\Certificates\CertificateBundle;
use TSSGGSModule\App\Libs\Certificates\CertificateService;
use TSSGGSModule\App\Libs\Certificates\IssuedMessage;
use TSSGGSModule\App\Libs\Products\ProductSettings;
use TSSGGSModule\App\Models\RemoteProduct;
use TSSGGSModule\App\Models\Request;
use TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;

/**
 * SSL certificate page by order state: awaiting configuration, submitted (validation, DNS record), active (details, subscription progress).
 * Part of SslCertificatePages (used there as a trait).
 */
trait SslStatusPages
{
    public function awaitingConfiguration($serviceId, $ssl)
    {
        $service = Service::where('id', $serviceId)->first();
        $order   = Order::where('id', $service->orderid)->first();
        $product = Product::where('id', $service->packageid)->first();

        $vars['product_title'] = sprintf(Text::get('product_title_awaiting_configuration'), $product->name);
        $vars['domain']        = $order->domain;
        $vars['product_name']  = $product->name;
        $vars['order_number']  = isset($order->ordernum) && !empty($order->ordernum) ? $order->ordernum : $order->id;
        $vars['url']           = (new Config())->getConfigureSSLUrl($ssl->id, $serviceId);
        // the page shows the same order and billing cards as the other product types
        $currency              = \getCurrency((int)$service->userid);
        $vars['billing']       = [
            'registration' => $this->smimeDate($service->regdate),
            'nextDue'      => $this->smimeDate($service->nextduedate),
            'amount'       => \formatCurrency($service->amount, $currency ? $currency['id'] : null),
            'cycle'        => $service->billingcycle,
        ];

        // a paid renewal waiting for these details: the client is told so, and until when the current certificate holds
        $renewalUntil         = \TSSGGSModule\App\Libs\Billing\RenewalPolicy::pendingRenewalUntil((int)$serviceId);
        $vars['renewalNotice'] = $renewalUntil !== '' ? sprintf(Text::get('sslRenewalPendingNotice'), $this->smimeDate($renewalUntil)) : '';

        // the certificate is bought already: its token completes the order here or in a hosting panel plugin (a
        // migrated order bought at payment: the token is read once)
        \TSSGGSModule\App\Libs\Certificates\IncompleteOrder::claimMigrated((int)$serviceId);
        $vars['certificateToken'] = \TSSGGSModule\App\Libs\Certificates\IncompleteOrder::token((int)$serviceId);
        $vars['serviceid']        = (int)$serviceId;

        return ['tpl' => 'awaiting_configuration', 'vars' => $vars];
    }

    public function configurationSubmitted($input, $ssl)
    {
        global $CONFIG;

        $vars = [];

        $service = Service::where('id', $input['id'])->first();
        $product = Product::where('id', $service->packageid)->first();
        $client  = Client::where('id', $service->userid)->first();

        $vars['configurableOptions'] = ProductConfigOptions::get($product->id, $input['id'], $service->billingcycle);

        // The stored copy is written when the order is placed; some gateways (GoGetSSL) only add the validation
        // details a few seconds later - re-read the order instead of showing an empty file / DNS block
        $orderDetails = CertificateService::refreshIfIncomplete($input['id'], (array)Request::getOrder($input['id']));
        $order        = $orderDetails['orderData'];
        $vars['serviceOpen']         = CertificateService::serviceOpen((int)$input['id']);
        // renewal / reissue in progress: the certificate issued before stays valid and downloadable
        $previous                    = $vars['serviceOpen'] ? CertificateService::previousCertificate((int)$input['id']) : [];
        $vars['previousValidTill']   = $previous ? date('F j, Y', strtotime($previous['orderFiles']['validity']['end'])) : '';
        $vars['previousNotice']      = $previous ? sprintf(Text::get('previous_certificate_valid'), $vars['previousValidTill']) : '';
        $vars['download_formats']    = CertificateBundle::enabledFormats();
        $vars['validationPreparing'] = $vars['serviceOpen'] && CertificateService::validationDataMissing($orderDetails);

        $remoteProductId = isset($order['orderData']['product']['id']) ? $order['orderData']['product']['id'] : $product->configoption2;
        $remoteProduct   = RemoteProduct::where('remoteId', $remoteProductId)->where('vendor', $product->configoption1)->first();
        $productAPI      = $remoteProduct ? $remoteProduct->toArray() : [];

        if($order['order']['status'] == 'active')
        {
            $certificate = $orderDetails['orderFiles'];
            return $this->certificateActive([
                                                'order'       => $order,
                                                'ssl'         => $ssl,
                                                'input'       => $input,
                                                'service'     => $service,
                                                'product'     => $product,
                                                'client'      => $client,
                                                'certificate' => $certificate
                                            ]);
        }

        $vars['product_title']                       = sprintf(Text::get('product_title_configuration'), $product->name);
        $vars['product_name']                        = $product->name;
        $vars['api_order_id']                        = $order['order']['id'];
        $vars['vendor_order_id']                     = is_array($order['items']) ? reset($order['items'])['vendor']['id'] : "";
        $vars['domain']                              = $service->domain;
        $vars['status']                              = $order['order']['status'];
        // the order waits at the vendor side (failed, unpaid, new order): an orange message, no API call
        $vars['vendorNotice']                        = in_array(\TSSGGSModule\App\Libs\Certificates\CertificateSyncHelpers::normalizeStatus($vars['status']), \TSSGGSModule\App\Libs\Certificates\CertificateSyncHelpers::NOTICE_STATUSES, true)
            ? Text::get('vendor_notice_' . \TSSGGSModule\App\Libs\Certificates\CertificateSyncHelpers::normalizeStatus($vars['status'])) : '';
        $vars['statusLabel']                         = $vars['vendorNotice'] !== '' ? Text::get('vendor_status_' . \TSSGGSModule\App\Libs\Certificates\CertificateSyncHelpers::normalizeStatus($vars['status'])) : '';
        $vars['valid_from']                          = '';
        $vars['valid_till']                          = '';
        $vars['subscribtion_starts']                 = '';
        $vars['subscribtion_ends']                   = '';
        $vars['next_reissue']                        = '';
        $vars['ssl_issuer']                          = '';
        $vars['validation_product']                  = isset($productAPI['validation']) ? $productAPI['validation'] : '';
        $vars['order_details']['registration_date']  = date('F j, Y', strtotime($service->regdate));
        $vars['order_details']['expiration_date']    = $service->nextduedate != '0000-00-00' ? date('F j, Y', strtotime($service->nextduedate)) : "";
        $vars['order_details']['reccuring_amount']   = formatCurrency($service->amount, $client->currency);
        $vars['order_details']['billing_cycle']      = ucfirst($service->billingcycle);
        $vars['order_details']['payment_method']     = self::paymentMethodName($service->paymentmethod);
        $vars['vendor_status']                       = is_array($order['items'] ?? null) ? (string)(reset($order['items'])['vendor']['status'] ?? '') : '';
        $vars['order_details']['csr_status_updated'] = date('Y-m-d H:i', strtotime($order['order']['date']));
        $vars['order_details_api']                   = $order;
        $vars['serviceid']                           = $service->id;
        $vars['countSAN']                            = 0;
        $vars['countSANWildcard']                    = 0;
        $domains                                     = is_array($order['items']) ? reset($order['items'])['domains'] : [];

        $mainDomain = $order['orderData']['common_name']['name'];

        $vars['additional_names'] = [];

        $vars['hasEmailValidation'] = false;
        // the approver address of e-mail validation (the gateway may report only "email")
        $approverEmails = \TSSGGSModule\App\Libs\Certificates\ApproverEmails::forService($service->id);

        foreach($domains as $domain)
        {
            if(strtolower($domain['method']) === 'email' || strpos((string)$domain['method'], '@') !== false)
            {
                $vars['hasEmailValidation'] = true;
            }

            $domain['dns']           = self::dnsRecord($domain['name'], $domain['method'], $domain['validation'] ?? [], (string)($order['orderData']['csr'] ?? ''));
            $domain['approverEmail'] = \TSSGGSModule\App\Libs\Certificates\ApproverEmails::addressFor($approverEmails, $domain['name'], $domain['method']);
            $domain['methodLabel']   = $domain['approverEmail'] !== '' ? 'EMAIL (' . $domain['approverEmail'] . ')' : self::methodLabel($domain['method']);
            $domain['method']      = strtolower((string)$domain['method']);
            $domain['approved']    = !empty($domain['approved']);

            // the method the gateway reports now - the one saved at enrollment is stale after "Change Approval Method"
            if($domain['name'] == $mainDomain)
            {
                $vars['mainDomainMethod']      = $domain['method'];
                $vars['mainDomainValidation']  = $domain['validation'];
                $vars['mainDomainDns']         = $domain['dns'];
                $vars['mainDomainMethodLabel'] = $domain['methodLabel'];
                $vars['mainDomainApproved']    = $domain['approved'];
            }
            else
            {
                $vars['additional_names'][] = $domain;
            }
        }

        $vars['whmcs_url'] = $CONFIG['SystemURL'];

        foreach(isset($order['orderData']['alternative_names']) ? (array)$order['orderData']['alternative_names'] : [] as $alternativeName)
        {
            $name = isset($alternativeName['name']) ? $alternativeName['name'] : (isset($alternativeName['domain']) ? $alternativeName['domain'] : '');
            strpos((string)$name, '*.') === 0 ? $vars['countSANWildcard']++ : $vars['countSAN']++;
        }

        if(isset($_SESSION['mod-success']))
        {
            $vars['success'] = $_SESSION['mod-success'];
            unset($_SESSION['mod-success']);
        }
        if(isset($_SESSION['mod-error']))
        {
            $vars['error'] = htmlspecialchars($_SESSION['mod-error']);
            unset($_SESSION['mod-error']);
        }

        $vars['autoinstall']      = AutoInstallHelpers::getStatusForView($service->id);
        $vars['csrfToken']        = \generate_token('plain');
        $vars['processing_guide'] = ProductSettings::message($product->id, 'processingGuide');
        // a CAA record that keeps the CA from issuing the names still waiting for validation (App\Libs\Certificates\CaaCheck)
        $waiting = [];
        foreach($domains as $domain)
        {
            if(empty($domain['approved']))
            {
                $waiting[] = (string)$domain['name'];
            }
        }
        $vars['caa_warning'] = \TSSGGSModule\Server\app\services\CaaWarning::pending((int)$service->id, $waiting);

        return ['tpl' => 'configuration_submitted', 'vars' => $vars];
    }

    /**
     * The DNS record a client has to create for "dns", "dns-cname" or "dns-txt" validation, null for other methods.
     * The gateway sends the value and, depending on the authority, the host and the record type; when the host is
     * missing the record goes on the domain itself.
     */
    public static function dnsRecord($domainName, $method, $validation, $csr = '')
    {
        $method = strtolower((string)$method);
        if(!in_array($method, ['dns', 'dns-cname', 'dns-txt'], true))
        {
            return null;
        }
        $validation = is_array($validation) ? $validation : [];
        $pick = function(array $keys) use ($validation) {
            foreach($keys as $key)
            {
                if(isset($validation[$key]) && is_scalar($validation[$key]) && trim((string)$validation[$key]) !== '')
                {
                    return trim((string)$validation[$key]);
                }
            }

            return '';
        };

        $type   = $method === 'dns-cname' ? 'CNAME' : ($method === 'dns-txt' ? 'TXT' : strtoupper($pick(['type', 'record_type'])));
        $domain = ltrim((string)$domainName, '*.');
        $value  = $pick(['value', 'target', 'content', 'record_value']);
        $host   = $pick(['name', 'host', 'record_name', 'record']);

        // GoGetSSL sends the whole record as one zone-file line in the value:
        //   "_A94C….whmcs2.example.com CNAME 7D00….sectigo.com"   or   "example.com.   IN   TXT   \"_rgkb…\""
        // split it into host, type and value so the client creates the right record
        if($host === '' && preg_match('/^(\S+)\s+(?:\d+\s+)?(?:IN\s+)?(CNAME|TXT)\s+(.+)$/i', $value, $record))
        {
            $host  = rtrim($record[1], '.');
            $type  = strtoupper($record[2]);
            $value = trim($record[3]);
            if(strlen($value) >= 2 && $value[0] === '"' && substr($value, -1) === '"')
            {
                $value = substr($value, 1, -1);
            }
            $value = $type === 'CNAME' ? rtrim($value, '.') : $value;
        }

        // Sectigo CNAME: the gateway sends only the target "<SHA-256 of the CSR, in two halves>.<unique>.sectigo.com";
        // the record name is "_<MD5 of the CSR>.<domain>" - computed here when the target belongs to this CSR
        if($host === '' && ($type === 'CNAME' || $method === 'dns') && $csr !== ''
            && preg_match('/^([0-9a-f]{32})\.([0-9a-f]{32})\.[^.]+\.(?:comodoca|sectigo)\.com\.?$/i', $value, $parts))
        {
            $der = base64_decode(preg_replace('/-----[^-]+-----|\s+/', '', (string)$csr), true);
            if($der !== false && strcasecmp(hash('sha256', $der), $parts[1] . $parts[2]) === 0)
            {
                $type = 'CNAME';
                $host = '_' . strtolower(md5($der)) . '.' . $domain;
            }
        }

        return [
            'type'  => $type,
            'host'  => $host !== '' ? $host : $domain,
            'value' => $value,
        ];
    }

    /** Display name of a payment gateway ("Bank Transfer"), not its system name ("banktransfer") */
    public static function paymentMethodName($gateway)
    {
        $name = \WHMCS\Database\Capsule::table('tblpaymentgateways')->where('gateway', (string)$gateway)->where('setting', 'name')->value('value');

        return $name !== null && $name !== '' ? (string)$name : ucfirst((string)$gateway);
    }

    /** "DNS (CNAME)", "HTTP", or the approver e-mail address */
    public static function methodLabel($method)
    {
        $method = (string)$method;
        $lower  = strtolower($method);
        if($lower === 'dns-cname' || $lower === 'dns-txt')
        {
            return 'DNS (' . strtoupper(substr($lower, 4)) . ')';
        }

        return in_array($lower, ['dns', 'http', 'https', 'email'], true) ? strtoupper($lower) : $method;
    }

    public function certificateActive($params)
    {
        global $CONFIG;

        $configuration = (new AddonModuleRepository())->getModuleConfiguration();

        $vars = [];

        $itemOrder = reset($params['order']['items']);

        $vars['product_title']                      = sprintf(Text::get('product_title_active'), $params['product']->name);
        $vars['product_name']                       = $params['product']->name;
        $vars['api_order_id']                       = $params['order']['order']['id'];
        $vars['vendor_order_id']                    = reset($params['order']['items'])['vendor']['id'];
        $vars['domain']                             = $params['service']->domain;
        $vars['status']                             = $params['order']['order']['status'];
        $vars['subscribtion_starts']                = date('F j, Y', strtotime($itemOrder['subscription']['begin']));
        $vars['subscribtion_ends']                  = date('F j, Y', strtotime($itemOrder['subscription']['end']));
        $vars['subscription_period']                = !empty($itemOrder['subscription']['begin']) && !empty($itemOrder['subscription']['end'])
            ? date('M j, Y', strtotime($itemOrder['subscription']['begin'])) . ' – ' . date('M j, Y', strtotime($itemOrder['subscription']['end']))
            : '';
        $vars['serviceid']                          = $params['service']->id;
        // cancelled / terminated: the page stays readable, but no downloads, sending, reissue or install
        $vars['certificateUsable']                  = CertificateService::usable((int)$params['service']->id);
        // a product addon's certificate: billed on the addon, no upgrades or extra SANs on the free service (M-14)
        $vars['addonNotice']                        = \TSSGGSModule\App\Libs\Certificates\AddonCertificates::clientNotice((int)$params['service']->id);
        $vars['order_details']['registration_date'] = date('F j, Y', strtotime($params['service']->regdate));
        $vars['order_details']['expiration_date']   = $params['service']->nextduedate != '0000-00-00' ? date('F j, Y', strtotime($params['service']->nextduedate)) : "";
        $vars['order_details']['reccuring_amount']  = formatCurrency($params['service']->amount, $params['client']->currency);
        $vars['order_details']['billing_cycle']     = ucfirst($params['service']->billingcycle);
        $vars['order_details']['payment_method']    = self::paymentMethodName($params['service']->paymentmethod);
        $vars['whmcs_url']                          = $CONFIG['SystemURL'];
        $vars['valid_from']                         = date('F j, Y', strtotime($params['certificate']['validity']['begin']));
        $vars['valid_till']                         = date('F j, Y', strtotime($params['certificate']['validity']['end']));

        $vars['visibleRenewButtonBeforeExpiration'] = $configuration['sslSettings']['visibleRenewButtonBeforeExpiration'];
        $vars['visibleRenewButtonAfterExpiration']  = $configuration['sslSettings']['visibleRenewButtonAfterExpiration'];

        $today                      = strtotime(date('Y-m-d'));
        // renewal is about the order: within it an expiring certificate is reissued, not renewed. Without an order end
        // from the gateway the service's due date stands in - never the certificate's own expiry
        $expiryDate                 = strtotime(!empty($itemOrder['subscription']['end']) ? $itemOrder['subscription']['end'] : (string)$params['service']->nextduedate);
        $vars['visibleRenewButton'] = false;
        if($configuration['sslSettings']['visibleRenewButtonBeforeExpiration'] && $today <= $expiryDate)
        {
            $vars['visibleRenewButton'] = true;
        }
        else if($configuration['sslSettings']['visibleRenewButtonAfterExpiration'] && $today > $expiryDate)
        {
            $vars['visibleRenewButton'] = true;
        }

        $vars['order_details_api'] = $params['order'];

        $vars['sendEmail'] = isset($_GET['sendEmail']) && $_GET['sendEmail'] == 'true' ? true : false;

        $now      = strtotime(date('Y-m-d'));
        $end_date = strtotime($vars['valid_till']);
        $datediff = $now - $end_date;

        $vars['next_reissue'] = sprintf(Text::get('next_reissue'), abs(round($datediff / (60 * 60 * 24))));

        if(isset($_SESSION['mod-error']))
        {
            $vars['errors'] = $_SESSION['mod-error'];
            unset($_SESSION['mod-error']);
        }

        if(isset($_SESSION['mod-success']))
        {
            $vars['success'] = $_SESSION['mod-success'];
            unset($_SESSION['mod-success']);
        }

        $vars['autoinstall'] = AutoInstallHelpers::getStatusForView($params['service']->id);

        // Certificate already installed via cPanel AutoInstall - do not tell the client it is still waiting for installation
        if(!empty($vars['autoinstall']) && $vars['autoinstall']['status'] === 'installed')
        {
            $vars['product_title'] = sprintf(Text::get('product_title_installed'), $params['product']->name);
        }

        // View certificate dialog
        $cert             = AutoInstallHelpers::extractCertificate($params['certificate'], isset($params['order']['orderData']['common_name']['name']) ? $params['order']['orderData']['common_name']['name'] : '');
        $vars['crt_code'] = $cert['crt'];
        $vars['ca_code']  = $cert['ca'];

        // Purchased vs used SANs ("Purchase additional SANs")
        $vars['configurableOptions'] = ProductConfigOptions::get($params['product']->id, $params['service']->id, $params['service']->billingcycle);
        $vars['countSAN']            = 0;
        $vars['countSANWildcard']    = 0;
        foreach(isset($params['order']['orderData']['alternative_names']) ? (array)$params['order']['orderData']['alternative_names'] : [] as $alternativeName)
        {
            $name = isset($alternativeName['name']) ? $alternativeName['name'] : (isset($alternativeName['domain']) ? $alternativeName['domain'] : '');
            strpos((string)$name, '*.') === 0 ? $vars['countSANWildcard']++ : $vars['countSAN']++;
        }

        $vars['issued_message']   = $this->issuedMessage((int)$params['product']->id);
        $vars['organization']     = isset($params['order']['orderData']['contacts']['organization']) ? array_filter((array)$params['order']['orderData']['contacts']['organization'], 'is_scalar') : [];
        $vars['download_formats'] = CertificateBundle::enabledFormats();
        try
        {
            // no intermediate certificates (e.g. issued straight from a root): do not offer an empty CA bundle
            if(trim(CertificateService::bundle((int)$params['service']->id)->ca()) === '')
            {
                $vars['download_formats'] = array_values(array_diff($vars['download_formats'], ['ca']));
            }
        }
        catch(\Exception $e)
        {
        }
        $vars['csrfToken']        = \generate_token('plain');

        // Install on server / Login to cPanel - only for clients with an active cPanel account
        $vars['cpanel_services'] = [];
        try
        {
            foreach(Cpanel::getServices($params['service']->userid) as $cpanelService)
            {
                $vars['cpanel_services'][] = ['id' => (int)$cpanelService->id, 'domain' => (string)$cpanelService->domain];
            }
        }
        catch(\Throwable $e)
        {
        }
        $aiState                     = AutoInstallHelpers::get($params['service']->id);
        // the client pastes the key only when none of the stored keys belongs to this certificate (own CSR)
        $vars['install_key_stored']  = CertificateService::privateKeyForCertificate($params['service']->id) !== '';
        $vars['install_domain']      = !empty($aiState['domain']) ? $aiState['domain'] : (CertificateService::installTarget($params['service']->id) ?: ltrim((string)$params['service']->domain, '*.'));
        // a certificate for public IPs is not installed on a cPanel account by domain
        $vars['install_available']   = !empty($vars['cpanel_services']) && (empty($vars['autoinstall']) || $vars['autoinstall']['status'] !== 'installed')
            && !\TSSGGSModule\App\Libs\Certificates\IpCertificate::isProduct((int)$params['service']->packageid);

        // Subscription status bar at the top of the page (same rules as the my.gogetssl.com SSL page)
        $vars['progress'] = $vars['certificateUsable'] ? self::subscriptionProgress(
            isset($params['certificate']['validity']['begin']) ? $params['certificate']['validity']['begin'] : '',
            isset($params['certificate']['validity']['end']) ? $params['certificate']['validity']['end'] : '',
            isset($itemOrder['subscription']['begin']) ? $itemOrder['subscription']['begin'] : '',
            isset($itemOrder['subscription']['end']) ? $itemOrder['subscription']['end'] : ''
        ) : null;

        return ['tpl' => 'certificate_active', 'vars' => $vars];
    }

    /**
     * Subscription status bar: where the current certificate sits in the subscription.
     *  - single: the certificate covers the whole subscription (one period);
     *  - first:  first certificate of a longer subscription (Current | Available);
     *  - middle: a reissued certificate with time left after it (Expired | Current | Available);
     *  - end:    the last certificate of the subscription (Expired | Current).
     * Dates are whole days; a subscription end within 3 days of the certificate expiry counts as the same date.
     */
    protected static function subscriptionProgress($sslBegin, $sslEnd, $subBegin, $subEnd)
    {
        $day = function($value) {
            $time = $value ? strtotime($value) : false;

            return $time ? strtotime(date('Y-m-d', $time)) : null;
        };

        $created = $day($sslBegin);
        $expires = $day($sslEnd);
        if(!$created || !$expires)
        {
            return null;
        }
        $begin = $day($subBegin) ?: $created;
        $end   = $day($subEnd) ?: $expires;
        if($begin > $created)
        {
            $begin = $created;
        }
        if($end <= $expires || ($end - $expires) <= 3 * 86400)
        {
            $end = $expires;
        }

        $today = strtotime(date('Y-m-d'));
        $days  = max(0, (int)round(($expires - $today) / 86400));
        $date  = function($time) {
            return date('Y-m-d', $time);
        };

        // labels: [date, caption key, tone]; each segment carries the label at its start and at its end
        $from    = [$date($created), 'pbValidFrom', 'green'];
        $till    = [$date($expires), 'pbValidTill', 'green'];
        $starts  = [$date($begin), 'pbSubStarts', 'muted'];
        $expired = ['expired', $created - $begin, $starts, null];
        if($begin == $created && $end == $expires)
        {
            // a single certificate longer than a year still has a reissue ahead, otherwise show when it expires
            $state    = 'single';
            $message  = strtotime('-1 year', $expires) > $today ? 'next' : 'expires';
            $segments = [['current', 1, $from, $till]];
        }
        elseif($begin == $created)
        {
            $state    = 'first';
            $message  = 'next';
            $segments = [['current', $expires - $created, $from, $till], ['future', $end - $expires, null, [$date($end), 'pbSubEnds', '']]];
        }
        elseif($end > $expires)
        {
            $state    = 'middle';
            $message  = 'next';
            $segments = [$expired, ['current', $expires - $created, $from, $till], ['future', $end - $expires, null, [$date($end), 'pbSubEnds', '']]];
        }
        else
        {
            $state    = 'end';
            $message  = 'ends';
            $segments = [$expired, ['current', $expires - $created, $from, [$date($end), 'pbSubEnds', 'red']]];
        }

        return [
            'state'    => $state,
            'days'     => $days,
            'daysKey'  => $days == 1 ? 'pbDay' : 'pbDays',
            'message'  => $message,
            'segments' => self::proportionalWidths($segments),
        ];
    }

    /**
     * Segment widths in % of the subscription, as long as each period really is. A short period still gets
     * enough room for its caption and dates (current 30%, others 18%); the rest is shared in proportion.
     */
    protected static function proportionalWidths(array $segments)
    {
        $total = 0;
        foreach($segments as $segment)
        {
            $total += max(0, $segment[1]);
        }
        $widths = [];
        foreach($segments as $i => $segment)
        {
            $widths[$i] = $total > 0 ? max(0, $segment[1]) / $total * 100 : 100 / count($segments);
        }

        // raise the segments under their minimum, take the room from the others in proportion (a few passes settle it)
        $fixed = [];
        for($pass = 0; $pass < count($segments); $pass++)
        {
            $changed = false;
            foreach($segments as $i => $segment)
            {
                $min = count($segments) == 1 ? 100 : ($segment[0] == 'current' ? 30 : 18);
                if(!isset($fixed[$i]) && $widths[$i] < $min)
                {
                    $widths[$i] = $min;
                    $fixed[$i]  = true;
                    $changed    = true;
                }
            }
            $left = 100 - array_sum(array_intersect_key($widths, $fixed));
            $free = array_sum(array_diff_key($widths, $fixed));
            foreach($widths as $i => $width)
            {
                if(!isset($fixed[$i]) && $free > 0)
                {
                    $widths[$i] = $width / $free * $left;
                }
            }
            if(!$changed)
            {
                break;
            }
        }

        foreach($segments as $i => $segment)
        {
            $segments[$i][1] = round($widths[$i], 2);
        }

        return $segments;
    }

    /**
     * Message shown once the certificate is issued (App\Libs\Certificates\IssuedMessage): the administrator's text, else the built-in
     * one of the certificate family in the client's language, with the installation guide of the authority
     */
    private function issuedMessage($productId)
    {
        return IssuedMessage::forClient($productId, Text::language());
    }
}
