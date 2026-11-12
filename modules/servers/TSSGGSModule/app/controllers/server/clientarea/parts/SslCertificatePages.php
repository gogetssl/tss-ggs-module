<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\controllers\server\clientarea\parts;

use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Client;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Order;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Product;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Service;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\SSL;
use ModulesGarden\Servers\TSSGGSModule\app\repository\whmcs\Config;
use ModulesGarden\Servers\TSSGGSModule\app\repository\whmcs\Domain;
use ModulesGarden\Servers\TSSGGSModule\app\repository\whmcs\ProductConfigOptions;
use ModulesGarden\Servers\TSSGGSModule\app\services\InvoiceRenew;
use ModulesGarden\Servers\TSSGGSModule\app\services\SSLStepThree;
use ModulesGarden\Servers\TSSGGSModule\core\Lang;
use ModulesGarden\Servers\TSSGGSModule\core\process\AbstractController;
use ModulesGarden\Servers\TSSGGSModule\Server;
use ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates;
use ModulesGarden\TSSGGSModule\App\Libs\AcmeHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\AisHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\AutoInstallHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\CertificateBundle;
use ModulesGarden\TSSGGSModule\App\Libs\CertificateService;
use ModulesGarden\TSSGGSModule\App\Libs\CertificateSyncHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\Cpanel;
use ModulesGarden\TSSGGSModule\App\Libs\GeneralSettings;
use ModulesGarden\TSSGGSModule\App\Libs\ProductSettings;
use ModulesGarden\TSSGGSModule\App\Libs\SmimeService;
use ModulesGarden\TSSGGSModule\App\Libs\CodeSigningService;
use ModulesGarden\TSSGGSModule\App\Libs\SslProductHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\Libs\SSLTrustCenterApi;
use ModulesGarden\TSSGGSModule\App\Models\AcmeSubscription;
use ModulesGarden\TSSGGSModule\App\Models\AcmeSubscriptionDomain;
use ModulesGarden\TSSGGSModule\App\Models\AisSubscription;
use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TSSGGSModule\App\Models\Request;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\ProductRepository;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Helper;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Invoice;
use WHMCS\Billing\Tax;
use WHMCS\Database\Capsule;

/**
 * SSL certificate of a service: status pages, downloads, reissue, validation, renewal invoice, CSR tools.
 * Part of the client area controller (clientarea\home): the methods keep their names, so the page URLs are unchanged.
 */
trait SslCertificatePages
{
    public function awaitingConfiguration($serviceId, $ssl)
    {
        $service = Service::where('id', $serviceId)->first();
        $order   = Order::where('id', $service->orderid)->first();
        $product = Product::where('id', $service->packageid)->first();

        $vars['product_title'] = sprintf(Lang::absoluteT('product_title_awaiting_configuration'), $product->name);
        $vars['domain']        = $order->domain;
        $vars['product_name']  = $product->name;
        $vars['order_number']  = isset($order->ordernum) && !empty($order->ordernum) ? $order->ordernum : $order->id;
        $vars['url']           = (new Config())->getConfigureSSLUrl($ssl->id, $serviceId);
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
        $vars['previousNotice']      = $previous ? sprintf(Lang::absoluteT('previous_certificate_valid'), $vars['previousValidTill']) : '';
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

        $vars['product_title']                       = sprintf(Lang::absoluteT('product_title_configuration'), $product->name);
        $vars['product_name']                        = $product->name;
        $vars['api_order_id']                        = $order['order']['id'];
        $vars['vendor_order_id']                     = is_array($order['items']) ? reset($order['items'])['vendor']['id'] : "";
        $vars['domain']                              = $service->domain;
        $vars['status']                              = $order['order']['status'];
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

        foreach($domains as $domain)
        {
            if(strtolower($domain['method']) === 'email' || strpos((string)$domain['method'], '@') !== false)
            {
                $vars['hasEmailValidation'] = true;
            }

            $domain['dns']         = self::dnsRecord($domain['name'], $domain['method'], $domain['validation'] ?? [], (string)($order['orderData']['csr'] ?? ''));
            $domain['methodLabel'] = self::methodLabel($domain['method']);
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

        if(isset($_SESSION['mg-success']))
        {
            $vars['success'] = $_SESSION['mg-success'];
            unset($_SESSION['mg-success']);
        }
        if(isset($_SESSION['mg-error']))
        {
            $vars['error'] = htmlspecialchars($_SESSION['mg-error']);
            unset($_SESSION['mg-error']);
        }

        $vars['autoinstall']      = AutoInstallHelpers::getStatusForView($service->id);
        $vars['csrfToken']        = \generate_token('plain');
        $vars['processing_guide'] = ProductSettings::message($product->id, 'processingGuide');

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

    public function sendCertificateHTML($input, $vars = [])
    {
        try
        {
            $this->requireCsrfToken();
            CertificateService::sendCertificate((int)$input['id']);
            redir('action=productdetails&id=' . (int)$input['id'] . '&sendEmail=true', 'clientarea.php');
        }
        catch(\Exception $e)
        {
            $_SESSION['mg-error'] = $e->getMessage();
            redir('action=productdetails&id=' . (int)$input['id'], 'clientarea.php');
        }
    }

    /**
     * Certificate download in any format of CertificateBundle::FORMATS (crt, ca, fullchain, nginx, apache, pkcs7, zip)
     */
    public function downloadHTML($input, $vars = [])
    {
        $format = isset($input['format']) ? (string)$input['format'] : 'zip';

        try
        {
            if(!in_array($format, CertificateBundle::enabledFormats(), true))
            {
                throw new \Exception(Lang::absoluteT('download_unknown_format'));
            }
            // the issued certificate, or during a renewal the previous one that is still valid
            CertificateBundle::output(CertificateService::downloadableBundle((int)$input['id'])->get($format));
        }
        catch(\Exception $e)
        {
            $_SESSION['mg-error'] = $e->getMessage();
            redir('action=productdetails&id=' . (int)$input['id'], 'clientarea.php');
        }
    }

    public function downloadCRTHTML($input, $vars = [])
    {
        return $this->downloadHTML(array_merge($input, ['format' => 'crt']), $vars);
    }

    public function downloadICRTHTML($input, $vars = [])
    {
        return $this->downloadHTML(array_merge($input, ['format' => 'ca']), $vars);
    }

    public function downloadSSLHTML($input, $vars = [])
    {
        return $this->downloadHTML(array_merge($input, ['format' => 'fullchain']), $vars);
    }

    public function reissueSSLHTML($input, $vars = [])
    {
        try
        {

            $serviceId   = $input['id'];
            CertificateService::requireUsable((int)$serviceId);
            $ssl         = SSL::getByServiceId($serviceId);
            $certificate = Request::getOrder($serviceId);

            $step = isset($input['step']) && !empty($input['step']) ? $input['step'] : 'one';

            if($step == 'one')
            {
                return $this->reissueStep1($input, $serviceId, $ssl, $certificate);
            }
            if($step == 'two')
            {
                return $this->reissueStep2($input, $serviceId, $ssl, $certificate);
            }


        }
        catch(\Exception $e)
        {

            return ['tpl' => 'error', 'vars' => ['error' => $e->getMessage()]];

        }

        return ['tpl' => 'home', 'vars' => $vars];

    }

    public function reissueStep1($input, $serviceId, $ssl, $certificate)
    {
        $countries = file_get_contents((defined('ROOTDIR') ? ROOTDIR : dirname(__DIR__, 8)) . DS . 'resources' . DS . 'country' . DS . 'dist.countries.json');

        $configuration = (new AddonModuleRepository())->getModuleConfiguration();

        $clientData = $input['params']['model']->client->toArray();
        if(!isset($configuration['sslSettings']['useProfileDetailsForCsr']) || empty($configuration['sslSettings']['useProfileDetailsForCsr']))
        {
            foreach($clientData as $key => $value)
            {
                $clientData[$key] = '';
            }
        }

        if(isset($configuration['sslSettings']['useProfileDetailsForCsr']) && !empty($configuration['sslSettings']['useProfileDetailsForCsr']))
        {
            $configuration['sslSettings']['defaultCsrCountry'] = false;
        }

        $vars              = [];
        $vars['countries'] = json_decode($countries, true);

        $vars['enable_csr']          = isset($configuration['sslSettings']['enableCsrGenerator']) ? $configuration['sslSettings']['enableCsrGenerator'] : false;
        $vars['use_profile_data']    = isset($configuration['sslSettings']['useProfileDetailsForCsr']) ? $configuration['sslSettings']['useProfileDetailsForCsr'] : false;
        $vars['default_csr_country'] = isset($configuration['sslSettings']['defaultCsrCountry']) ? $configuration['sslSettings']['defaultCsrCountry'] : false;
        $vars['domain']              = $input['params']['domain'];
        $vars['client']              = $clientData;

        $params                = $input['params'];
        $vars['sans']          = isset($params['configoptions']['sans']) ? $params['configoptions']['sans'] : 0;
        $vars['sans_wildcard'] = isset($params['configoptions']['sans_wildcard']) ? $params['configoptions']['sans_wildcard'] : 0;

        $vars['sans']              += (int)$params['configoption7'];
        $vars['sans_wildcard']     += (int)$params['configoption8'];
        $vars['serviceid']         = $input['id'];

        // Reissue with the AutoInstall key keeps the automatic installation; more SANs can be bought before the reissue
        $autoInstall             = AutoInstallHelpers::get($serviceId);
        $vars['autoinstall_key'] = !empty($autoInstall['private_key']) && $autoInstall['status'] !== AutoInstallHelpers::STATUS_DISABLED;
        $sanOptions              = ProductConfigOptions::get($params['packageid'], $serviceId, $params['model']->billingcycle);
        $vars['buy_sans_url']    = $sanOptions ? 'upgrade.php?type=configoptions&id=' . (int)$serviceId : '';
        $vars['lang_san']          = sprintf(Lang::absoluteT('sans_additional'), $vars['sans']);
        $vars['lang_san_wildcard'] = sprintf(Lang::absoluteT('sans_additional_wildcard'), $vars['sans_wildcard']);

        $vars['errors']            = isset($_SESSION['mg-error']) ? $_SESSION['mg-error'] : false;
        $vars['post_csr']          = isset($_SESSION['post_csr']) ? $_SESSION['post_csr'] : false;
        $vars['post_san']          = isset($_SESSION['post_san']) ? $_SESSION['post_san'] : false;
        $vars['post_san_wildcard'] = isset($_SESSION['post_san_wildcard']) ? $_SESSION['post_san_wildcard'] : false;
        // first visit: the SANs of the current certificate, so a reissue does not silently drop them
        if($vars['post_san'] === false && $vars['post_san_wildcard'] === false)
        {
            $current                   = CertificateService::currentSans((int)$serviceId);
            $vars['post_san']          = implode(', ', $current['single']);
            $vars['post_san_wildcard'] = implode(', ', $current['wildcard']);
        }

        if(isset($_SESSION['mg-error']))
        {
            unset($_SESSION['mg-error']);
        }
        if(isset($_SESSION['post_csr']))
        {
            unset($_SESSION['post_csr']);
        }
        if(isset($_SESSION['post_san']))
        {
            unset($_SESSION['post_san']);
        }
        if(isset($_SESSION['post_san_wildcard']))
        {
            unset($_SESSION['post_san_wildcard']);
        }

        return ['tpl' => 'reissue_step1', 'vars' => $vars];
    }

    public function reissueStep2($input, $serviceId, $ssl, $certificate)
    {
        $vars = [];
        $vars['csrfToken'] = \generate_token('plain');

        try
        {

            // The step 1 form posts fields[sans_domains] / fields[sans_domains_wildcard]; step 2 re-posts them flat
            $fields = [
                'sans_domains' => isset($input['fields']['sans_domains']) ? $input['fields']['sans_domains'] : (isset($input['sans_domains']) ? $input['sans_domains'] : ''),
                'wildcard_san' => isset($input['fields']['sans_domains_wildcard']) ? $input['fields']['sans_domains_wildcard'] : (isset($input['sans_domains_wildcard']) ? $input['sans_domains_wildcard'] : ''),
            ];
            $fields = array_map(function($list) {
                return implode(',', array_filter(array_map('trim', preg_split('/[\s,;]+/', (string)$list))));
            }, $fields);

            if(!empty($input['use_autoinstall_key']))
            {
                $currentCn     = isset($certificate['orderData']['orderData']['common_name']['name']) ? $certificate['orderData']['orderData']['common_name']['name'] : $input['params']['domain'];
                $input['csr']  = CertificateService::csrFromAutoInstallKey($serviceId, $currentCn);
                $_POST['csr']  = $input['csr'];
                unset($_POST['use_autoinstall_key']);
            }
            $_POST['sans_domains']          = $fields['sans_domains'];
            $_POST['sans_domains_wildcard'] = $fields['wildcard_san'];
            unset($_POST['fields']);

            $decode  = $this->decodeCSR($input['csr']);
            $domains = $this->validateDomains($decode['CN'], $fields, $input['params']);

            $configProduct = (new ProductRepository())->getProductConfiguration($input['params']['packageid']);
            $addonConfig   = (new AddonModuleRepository())->getModuleConfiguration();

            $available_method_validation = explode(',', $configProduct['dcv']);
            if(isset($addonConfig['sslSettings']['disableEmailValidation']) && !empty($addonConfig['sslSettings']['disableEmailValidation']))
            {
                $key = array_search('email', $available_method_validation);
                if($key !== false) unset($available_method_validation[$key]);

                $temp_available_method_validation = [];
                foreach($available_method_validation as $method)
                {
                    $temp_available_method_validation[] = $method;
                }
                $available_method_validation = $temp_available_method_validation;
            }
            $vars['available_method_validation'] = json_encode($available_method_validation);

            $api               = SSLTrustCenterApi::forVendor($configProduct['provider'], $configProduct['category']);
            $approvers         = $api->getDomainEmails($domains);
            $vars['approvers'] = $this->approversToOptions($approvers);

            if(isset($input['customAction']) && $input['customAction'] == 'sendReissue')
            {
                $vars['error'] = $this->reissueStep3($input);
            }


        }
        catch(\Exception $e)
        {

            $_SESSION['post_csr']          = $input['csr'];
            $_SESSION['post_san']          = $input['sans_domains'];
            $_SESSION['post_san_wildcard'] = $input['sans_domains_wildcard'];
            $_SESSION['mg-error']          = $e->getMessage();
            redir('action=productdetails&id=' . $input['id'] . '&mg-action=reissueSSL', 'clientarea.php');

        }

        $vars['postData'] = $_POST;

        return ['tpl' => 'reissue_step2', 'vars' => $vars];
    }

    public function reissueStep3($input)
    {
        try
        {
            $this->requireCsrfToken();
            $serviceId = (int)$input['id'];
            $methods   = [];
            foreach((array)$input['method'] as $domain => $method)
            {
                $methods[$domain] = $method == 'email' ? $input['approver'][$domain] : $method;
            }

            // CN first
            $commonName = openssl_csr_get_subject($input['csr'])['CN'];
            if(isset($methods[$commonName]))
            {
                $methods = [$commonName => $methods[$commonName]] + $methods;
            }

            CertificateService::reissue($serviceId, $input['csr'], $methods);

            $_SESSION['mg-success'] = Lang::absoluteT('reissue_success');
            redir('action=productdetails&id=' . $serviceId, 'clientarea.php');
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }


    public function decodeCSR($csr)
    {
        $decode = openssl_csr_get_subject($csr);
        if($decode === false)
        {
            throw new \Exception(Lang::absoluteT('csr_error'));
        }
        CertificateService::assertCsrKey($csr);

        return $decode;
    }

    public function validateDomains($commonName, $fields, $params = [])
    {
        $countSan         = isset($params['configoptions']['sans']) ? $params['configoptions']['sans'] : 0;
        $countSanWildCard = isset($params['configoptions']['sans_wildcard']) ? $params['configoptions']['sans_wildcard'] : 0;

        $countSan         += $params['configoption7'];
        $countSanWildCard += $params['configoption8'];

        // lower case, no duplicates, never the common name itself (it would use a paid SAN for nothing)
        foreach(['sans_domains', 'wildcard_san'] as $key)
        {
            if(!empty($fields[$key]))
            {
                $list         = array_unique(array_filter(array_map(function($name) {
                    return strtolower(Domain::toAscii(trim($name)));
                }, explode(',', (string)$fields[$key]))));
                $fields[$key] = implode(',', array_diff($list, [strtolower(trim((string)$commonName))]));
            }
        }

        $domainValidation = new Domain();

        $domains = '';
        if(!empty($fields['sans_domains']))
        {

            $san = explode(',', $fields['sans_domains']);

            if($countSan < count($san))
            {
                throw new \Exception(sprintf(Lang::absoluteT('using_max_san'), $countSan));
            }

            $ipAllowed = !empty($params['packageid']) && $this->sanIpAllowed($params['packageid']);
            foreach($san as $domain)
            {
                if(empty($domain)) continue;
                $domainValidation->validateDomain($domain, false, $ipAllowed);
            }

            $domains .= $fields['sans_domains'];
        }
        if(!empty($fields['wildcard_san']))
        {

            $wildcard = explode(',', $fields['wildcard_san']);

            if($countSanWildCard < count($wildcard))
            {
                throw new \Exception(sprintf(Lang::absoluteT('using_max_san_wildcard'), $countSanWildCard));
            }

            foreach($wildcard as $domain)
            {
                if(empty($domain)) continue;
                $domainValidation->validateDomain($domain, true);
            }

            $domains .= ',' . $fields['wildcard_san'];
        }
        if(empty($domains))
        {
            $domains = $commonName;
        }
        else
        {
            $domains = $commonName . ',' . $domains;
        }
        $domains = explode(',', $domains);

        foreach($domains as $key => $domain)
        {
            if(empty($domain)) unset($domains[$key]);
            $domains[$key] = trim($domain);
        }

        return $domains;
    }

    public function approversToOptions($approvers)
    {
        $results = [];
        foreach($approvers as $approver)
        {
            $options = '';
            foreach($approver['emails'] as $option)
            {
                $options .= '<option value="' . $option . '">' . $option . '</option>';
            }
            $results[$approver['domain']] = $options;
        }
        return $results;
    }


    public function certificateActive($params)
    {
        global $CONFIG;

        $configuration = (new AddonModuleRepository())->getModuleConfiguration();

        $vars = [];

        $itemOrder = reset($params['order']['items']);

        $vars['product_title']                      = sprintf(Lang::absoluteT('product_title_active'), $params['product']->name);
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
        // renewal is about the subscription: within it an expiring certificate is reissued, not renewed
        $expiryDate                 = strtotime(!empty($itemOrder['subscription']['end']) ? $itemOrder['subscription']['end'] : $params['certificate']['validity']['end']);
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

        $vars['next_reissue'] = sprintf(Lang::absoluteT('next_reissue'), abs(round($datediff / (60 * 60 * 24))));

        if(isset($_SESSION['mg-error']))
        {
            $vars['errors'] = $_SESSION['mg-error'];
            unset($_SESSION['mg-error']);
        }

        if(isset($_SESSION['mg-success']))
        {
            $vars['success'] = $_SESSION['mg-success'];
            unset($_SESSION['mg-success']);
        }

        $vars['autoinstall'] = AutoInstallHelpers::getStatusForView($params['service']->id);

        // Certificate already installed via cPanel AutoInstall - do not tell the client it is still waiting for installation
        if(!empty($vars['autoinstall']) && $vars['autoinstall']['status'] === 'installed')
        {
            $vars['product_title'] = sprintf(Lang::absoluteT('product_title_installed'), $params['product']->name);
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

        $vars['issued_message']   = ProductSettings::message($params['product']->id, 'issuedMessage');
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
        $vars['install_available']   = !empty($vars['cpanel_services']) && (empty($vars['autoinstall']) || $vars['autoinstall']['status'] !== 'installed');

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
     * "Install on server" - installs the issued certificate on the client's cPanel account
     */
    public function installCertificateHTML($input, $vars = [])
    {
        $serviceId = (int)$input['id'];

        try
        {
            $this->requireCsrfToken();
            CertificateService::requireUsable($serviceId);
            if(!$this->acquireServiceLock($serviceId))
            {
                throw new \Exception(Lang::absoluteT('sslConfigInProgress'));
            }
            try
            {
                CertificateService::installOnCpanel($serviceId, isset($input['install_domain']) ? $input['install_domain'] : '', isset($input['private_key']) ? $input['private_key'] : '');
            }
            finally
            {
                $this->releaseServiceLock($serviceId);
            }
            $_SESSION['mg-success'] = Lang::absoluteT('install_success');
        }
        catch(\Exception $e)
        {
            $_SESSION['mg-error'] = Lang::absoluteT('install_error') . ' ' . $e->getMessage();
        }

        redir('action=productdetails&id=' . $serviceId, 'clientarea.php');
    }

    public function renewHTML($input, $vars = [])
    {
        global $CONFIG;

        try
        {
            $this->requireCsrfToken();

            list($invoiceId, $created) = (new InvoiceRenew())->getOrCreateInvoice((int)$input['id']);

            $_SESSION['mg-success'] = Lang::absoluteT($created ? 'renew_invoice_generated' : 'renew_exists') . ' <a target="_blank" href="' . $CONFIG['SystemURL'] . '/viewinvoice.php?id=' . (int)$invoiceId . '">' . Lang::absoluteT('Click here to view the invoice') . '</a>';
            redir('action=productdetails&id=' . $input['id'], 'clientarea.php');

        }
        catch(\Exception $e)
        {

            $_SESSION['mg-error'] = $e->getMessage();
            redir('action=productdetails&id=' . $input['id'], 'clientarea.php');

        }


    }

    public function checkCSRJSON($input, $vars = [])
    {
        $decode = openssl_csr_get_subject($input['csr']);
        return ['json' => ['status' => 'success', 'data' => $decode]];
    }

    public function generateCSRJSON($input, $vars = [])
    {
        try
        {

            $privateKey = openssl_pkey_new(["private_key_bits" => 2048, "private_key_type" => OPENSSL_KEYTYPE_RSA]);
            openssl_pkey_export($privateKey, $pKeyOut);

            $csr = openssl_csr_new([
                'countryName' => strtoupper($input['country']),
                'stateOrProvinceName' => $input['state'],
                'localityName' => $input['locality'],
                'organizationName' => $input['organization'],
                'organizationalUnitName' => $input['unit_name'],
                'commonName' => $input['common_name'],
                'emailAddress' => $input['email_address'],
            ], $privateKey);

            if(!$csr)
            {
                return ['json' => ['status' => 'error', 'message' => Lang::absoluteT('error_csr_generate')]];
            }

            openssl_csr_export($csr, $csrOut);

            return ['json' => ['status' => 'success', 'data' => ['csr' => $csrOut, 'private_key' => \ModulesGarden\TSSGGSModule\App\Libs\PrivateKeyDownload::store((int)$input['id'], $pKeyOut)]]];

        }
        catch(\Exception $e)
        {

            return ['json' => ['status' => 'error', 'message' => $e->getMessage()]];

        }

    }

    public function downloadKeyHTML($input, $vars = [])
    {
        // POST + token, and only a key this client's session generated for this service (no URL, no decrypt oracle)
        try
        {
            $this->requireCsrfToken();
            $decodeKey = \ModulesGarden\TSSGGSModule\App\Libs\PrivateKeyDownload::fetch((int)$input['id'], (string)($_POST['handle'] ?? ''));
            if($decodeKey === null)
            {
                throw new \Exception(Lang::absoluteT('private_key_expired'));
            }
        }
        catch(\Exception $e)
        {
            $_SESSION['mg-error'] = $e->getMessage();
            redir('action=productdetails&id=' . (int)$input['id'], 'clientarea.php');
        }
        $keyDomain = (string)Service::where('id', (int)$input['id'])->value('domain');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . CertificateBundle::fileName($keyDomain !== '' ? $keyDomain : 'private') . '.key"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        print $decodeKey;
        exit;

    }

    public function cancelHTML($input, $vars = [])
    {
        try
        {
            $this->requireCsrfToken();
        }
        catch(\Exception $e)
        {
            $_SESSION['mg-error'] = $e->getMessage();
            redir('action=productdetails&id=' . (int)$input['id'], 'clientarea.php');
        }
        // the same cancellation as Terminate / admin "Cancel Order" (vendor order, stored statuses, WHMCS SSL order,
        // AutoInstall tasks), then the service itself
        try
        {
            if(!CertificateService::serviceOpen((int)$input['id']))
            {
                throw new \Exception('The certificate is not available: the service has been cancelled or terminated.');
            }
            CertificateService::cancel((int)$input['id'], 'Cancelled by customer');
            Service::where('id', (int)$input['id'])->update(['domainstatus' => 'Cancelled']);
            CertificateService::forgetPrivateKeys((int)$input['id']);
        }
        catch(\Exception $e)
        {
            $_SESSION['mg-error'] = $e->getMessage();
        }

        redir('action=productdetails&id=' . (int)$input['id'], 'clientarea.php');
    }

    public function revalidateHTML($input, $vars = [])
    {
        $serviceId = (int)$input['id'];

        try
        {
            $this->requireCsrfToken();

            $ssl     = SSL::getByServiceId($serviceId);
            $service = Service::where('id', $serviceId)->first();
            if(!$ssl || !$service || $ssl->status !== 'Configuration Submitted' || empty($ssl->remoteid) || !CertificateService::serviceOpen($serviceId))
            {
                throw new \Exception(Lang::absoluteT('revalidate_not_available'));
            }

            $this->getSslApiForProduct($service->packageid)->recheckValidation($ssl->remoteid);

            // Same flow as the SSLCertificates cron - fetch the order details right away
            $status      = '';
            $certificate = CertificateSyncHelpers::getCertificatesQuery()->where('tblhosting.id', $serviceId)->first();
            if($certificate)
            {
                $status = CertificateSyncHelpers::syncCertificate($certificate->toArray(), (new AddonModuleRepository())->getModuleConfiguration());
            }

            $_SESSION['mg-success'] = Lang::absoluteT($status === 'active' ? 'revalidate_success_active' : 'revalidate_success_pending');
        }
        catch(\Exception $e)
        {
            $_SESSION['mg-error'] = Lang::absoluteT('revalidate_error') . ' ' . $e->getMessage();
        }

        redir('action=productdetails&id=' . $serviceId, 'clientarea.php');
    }

    public function resendValidationEmailHTML($input, $vars = [])
    {
        $serviceId = (int)$input['id'];

        try
        {
            $this->requireCsrfToken();

            $ssl     = SSL::getByServiceId($serviceId);
            $service = Service::where('id', $serviceId)->first();
            if(!$ssl || !$service || $ssl->status !== 'Configuration Submitted' || empty($ssl->remoteid) || !CertificateService::serviceOpen($serviceId))
            {
                throw new \Exception(Lang::absoluteT('resend_dcv_not_available'));
            }

            $this->getSslApiForProduct($service->packageid)->resendValidationEmail($ssl->remoteid);

            $_SESSION['mg-success'] = Lang::absoluteT('resend_dcv_success');
        }
        catch(\Exception $e)
        {
            $_SESSION['mg-error'] = Lang::absoluteT('resend_dcv_error') . ' ' . $e->getMessage();
        }

        redir('action=productdetails&id=' . $serviceId, 'clientarea.php');
    }

    public function downloadCSRHTML($input, $vars = [])
    {
        $serviceId   = $input['id'];
        $certificate = Request::getOrder($serviceId);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $certificate['orderData']['orderData']['common_name']['name'] . '.csr');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        print $certificate['orderData']['orderData']['csr'];
        exit;
    }

    public function downloadAuthFileHTML($input, $vars = [])
    {
        $serviceId   = $input['id'];
        $certificate = Request::getOrder($serviceId);

        $mainDomainValidation = $certificate['orderData']['orderData']['common_name']['name'];

        $items = reset($certificate['orderData']['items']);

        $fileName    = '';
        $fileContent = '';

        foreach($items['domains'] as $domain)
        {
            if($domain['name'] == $mainDomainValidation && ($domain['method'] == 'http' || $domain['method'] == 'https'))
            {
                $fileName    = $domain['validation']['file_name'];
                $fileContent = $domain['validation']['value'];
            }
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $fileName);
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        print $fileContent;
        exit;
    }

    public function changeApprovalMethodHTML($input, $vars = [])
    {
        $serviceId   = $input['id'];
        if(!CertificateService::serviceOpen((int)$serviceId))
        {
            return ['tpl' => 'error', 'vars' => ['error' => 'The certificate is not available: the service has been cancelled or terminated.']];
        }
        $certificate = Request::getOrder($serviceId);
        $service     = Service::where('id', $serviceId)->first();

        $configProduct = (new ProductRepository())->getProductConfiguration($service->packageid);
        $addonConfig   = (new AddonModuleRepository())->getModuleConfiguration();

        $available_method_validation = explode(',', $configProduct['dcv']);
        if(isset($addonConfig['sslSettings']['disableEmailValidation']) && !empty($addonConfig['sslSettings']['disableEmailValidation']))
        {
            $key = array_search('email', $available_method_validation);
            if($key !== false) unset($available_method_validation[$key]);

            $temp_available_method_validation = [];
            foreach($available_method_validation as $method)
            {
                $temp_available_method_validation[] = $method;
            }
            $available_method_validation = $temp_available_method_validation;
        }
        $vars['available_method_validation'] = $available_method_validation;

        $api = SSLTrustCenterApi::forVendor($configProduct['provider'], $configProduct['category']);

        if($_POST)
        {
            $domainChanges = [];
            $newOrderData  = $certificate['orderData']['orderData'];
            $orderDomains  = array_merge([(string)($newOrderData['common_name']['name'] ?? '')], array_column((array)($newOrderData['alternative_names'] ?? []), 'name'));
            $emailDomains  = [];
            try
            {
                $this->requireCsrfToken();
                foreach((array)($_POST['method'] ?? []) as $domainName => $methodDomain)
                {
                    // only the domains of this order and the methods the product offers
                    if(!in_array((string)$domainName, $orderDomains, true) || !in_array((string)$methodDomain, $available_method_validation, true))
                    {
                        throw new \Exception(Lang::absoluteT('invalidValidationMethod'));
                    }
                    if($methodDomain == 'email')
                    {
                        $emailDomains[] = (string)$domainName;
                    }
                }
                // an approver address must be one the certificate authority offers for that domain
                $offered = [];
                foreach($emailDomains ? (array)$api->getDomainEmails($emailDomains) : [] as $row)
                {
                    $offered[(string)($row['domain'] ?? '')] = array_map('strtolower', (array)($row['emails'] ?? []));
                }
                foreach($emailDomains as $domainName)
                {
                    if(!in_array(strtolower((string)($_POST['approver'][$domainName] ?? '')), $offered[$domainName] ?? [], true))
                    {
                        throw new \Exception(Lang::absoluteT('invalidValidationMethod'));
                    }
                }
            }
            catch(\Exception $exception)
            {
                $_SESSION['mg-error'] = $exception->getMessage();
                redir('action=productdetails&id=' . $serviceId, 'clientarea.php');
            }
            foreach((array)($_POST['method'] ?? []) as $domainName => $methodDomain)
            {
                $methodValidaton = $methodDomain == 'email' ? $_POST['approver'][$domainName] : $methodDomain;
                $domainChanges[] = ['domain' => ['name' => $domainName, 'validation_method' => $methodValidaton]];

                if($domainName == $newOrderData['common_name']['name'])
                {
                    $newOrderData['common_name']['validation_method'] = $methodValidaton;
                }

                foreach((array)($newOrderData['alternative_names'] ?? []) as $key => $san)
                {
                    if($san['name'] == $domainName)
                    {
                        $newOrderData['alternative_names'][$key]['validation_method'] = $methodValidaton;
                    }
                }
            }
            try
            {

                $itemId = (int)SSL::getByServiceId($serviceId)->remoteid;
                foreach($domainChanges as $domainChange)
                {
                    $api->changeApprovers($itemId, $domainChange);
                }
                $newData                               = $api->getOrder($itemId, $serviceId);
                $certificate['orderData']['orderData'] = $newOrderData;
                $certificate['orderData']['order']     = $newData['order'];
                $certificate['orderData']['items']     = $newData['items'];
                Request::updateOrderData($serviceId, $certificate);
                CertificateService::event($serviceId, 'info', '[Validation] Validation method changed by the client');
                $_SESSION['mg-success'] = Lang::absoluteT('validation_method_changed');

            }
            catch(\Exception $exception)
            {
                $_SESSION['mg-error'] = $exception->getMessage();
            }

            redir('action=productdetails&id=' . $serviceId, 'clientarea.php');
        }

        $domains   = [];
        $domains[] = $certificate['orderData']['orderData']['common_name']['name'];
        foreach((array)($certificate['orderData']['orderData']['alternative_names'] ?? []) as $san)
        {
            $domains[] = $san['name'];
        }
        $vars['domains']   = $domains;

        // each domain starts on the method it uses now - as the gateway reports it (the enrollment data goes stale
        // after a change); an address is e-mail, dns-cname / dns-txt stay as they are when the product offers them
        $current = [$certificate['orderData']['orderData']['common_name']['name'] => $certificate['orderData']['orderData']['common_name']['validation_method'] ?? ''];
        foreach((array)($certificate['orderData']['orderData']['alternative_names'] ?? []) as $san)
        {
            $current[$san['name']] = $san['validation_method'] ?? '';
        }
        $gatewayItems = isset($certificate['orderData']['items']) && is_array($certificate['orderData']['items']) ? $certificate['orderData']['items'] : [];
        foreach((array)(reset($gatewayItems)['domains'] ?? []) as $gatewayDomain)
        {
            if(isset($current[$gatewayDomain['name'] ?? '']) && !empty($gatewayDomain['method']))
            {
                $current[$gatewayDomain['name']] = $gatewayDomain['method'];
            }
        }
        $vars['currentMethods'] = array_map(function($method) use ($available_method_validation) {
            $method = strtolower((string)$method);
            if(strpos($method, '@') !== false)
            {
                return 'email';
            }

            return strpos($method, 'dns') === 0 && !in_array($method, $available_method_validation, true) ? 'dns' : $method;
        }, $current);

        $approvers         = $api->getDomainEmails($domains);
        $vars['approvers'] = $this->approversToOptions($approvers);

        return ['tpl' => 'change_approval_method', 'vars' => $vars];
    }
}
