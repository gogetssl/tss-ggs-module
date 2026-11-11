<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\controllers\server\clientarea;

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

class home extends AbstractController
{

    function indexHTML($input, $vars = [])
    {
        $vars['csrfToken'] = \generate_token('plain');

        try
        {
            $serviceId = $input['id'];
            $ssl       = SSL::getByServiceId($serviceId);

            $service = \WHMCS\Service\Service::find($serviceId);

            if(SmimeService::isSmimeWhmcsProduct($service->packageid))
            {
                return $this->indexSmimeHTML($input, $vars);
            }

            if(AisHelpers::isAisWhmcsProduct($service->packageid))
            {
                if(isset($_GET['aisconfig']) && (string)$_GET['aisconfig'] === '1')
                {
                    return $this->aisConfigurationHTML($input, $vars);
                }
                return $this->indexAisHTML($input, $vars);
            }

            if(AcmeHelpers::isAcmeWhmcsProduct($service->packageid))
            {
                if(isset($_GET['acmeconfig']) && (string)$_GET['acmeconfig'] === '1')
                {
                    return $this->acmeConfigurationHTML($input, $vars);
                }
                return $this->indexAcmeHTML($input, $vars);
            }

            if(isset($ssl->status) && $ssl->status == 'Awaiting Configuration')
            {
                if(isset($_GET['configssl']) && (string)$_GET['configssl'] === '1')
                {
                    return $this->sslConfigurationHTML($input, $vars);
                }
                return $this->awaitingConfiguration($serviceId, $ssl);
            }

            if(isset($ssl->status) && $ssl->status == 'Configuration Submitted')
            {
                return $this->configurationSubmitted($input, $ssl);
            }

        }
        catch(\Exception $e)
        {

            return ['tpl' => 'error', 'vars' => ['error' => $e->getMessage()]];

        }

        return ['tpl' => 'home', 'vars' => $vars];

    }

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

        foreach($order['orderData']['alternative_names'] as $alternativeName)
        {
            if(strpos($alternativeName['domain'], '*.') !== false)
            {
                $vars['countSANWildcard']++;
            }
            else
            {
                $vars['countSAN']++;
            }
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
        $countries = file_get_contents(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__DIR__))))))) . DS . 'resources' . DS . 'country' . DS . 'dist.countries.json');

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

            $provider    = strtolower($configProduct['provider']);
            $credentials = $addonConfig['credentials'][$provider];

            if($provider == 'tss' && $credentials['OperationMode'] == 'sandbox')
            {
                $credentials['PartnerCode'] = $credentials['TestPartnerCode'];
                $credentials['AuthToken']   = $credentials['TestAuthToken'];
            }

            $api               = new SSLTrustCenterApi($configProduct['provider'], $credentials['PartnerCode'], $credentials['AuthToken'], $configProduct['category']);
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
        if($configuration['sslSettings']['visibleRenewButtonBeforeExpiration'] && $today < $expiryDate)
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

        $provider    = strtolower($configProduct['provider']);
        $credentials = $addonConfig['credentials'][$provider];

        if($provider == 'tss' && $credentials['OperationMode'] == 'sandbox')
        {
            $credentials['PartnerCode'] = $credentials['TestPartnerCode'];
            $credentials['AuthToken']   = $credentials['TestAuthToken'];
        }

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

        $api = new SSLTrustCenterApi($configProduct['provider'], $credentials['PartnerCode'], $credentials['AuthToken'], $configProduct['category']);

        if($_POST)
        {
            $domainChanges = [];
            $newOrderData  = $certificate['orderData']['orderData'];
            foreach($_POST['method'] as $domainName => $methodDomain)
            {
                $methodValidaton = $methodDomain == 'email' ? $_POST['approver'][$domainName] : $methodDomain;
                $domainChanges[] = ['domain' => ['name' => $domainName, 'validation_method' => $methodValidaton]];

                if($domainName == $newOrderData['common_name']['name'])
                {
                    $newOrderData['common_name']['validation_method'] = $methodValidaton;
                }

                foreach($newOrderData['alternative_names'] as $key => $san)
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
        foreach($certificate['orderData']['orderData']['alternative_names'] as $san)
        {
            $domains[] = $san['name'];
        }
        $vars['domains']   = $domains;

        // each domain starts on the method it uses now - as the gateway reports it (the enrollment data goes stale
        // after a change); an address is e-mail, dns-cname / dns-txt stay as they are when the product offers them
        $current = [$certificate['orderData']['orderData']['common_name']['name'] => $certificate['orderData']['orderData']['common_name']['validation_method'] ?? ''];
        foreach($certificate['orderData']['orderData']['alternative_names'] as $san)
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

//ACME==================================================================================================================
    private function getAcmeDomainLimits(array $params)
    {
        $singleIncluded = isset($params['configoption7']) ? (int)$params['configoption7'] : 0;
        $singleBought   = isset($params['configoptions']['sans']) ? (int)$params['configoptions']['sans'] : 0;
        $singleLimit    = $singleIncluded + $singleBought;

        if(Helpers::isWildcardEnabled((int)$params['packageid']))
        {
            $wildcardIncluded = isset($params['configoption8']) ? (int)$params['configoption8'] : 0;
            $wildcardBought   = isset($params['configoptions']['sans_wildcard']) ? (int)$params['configoptions']['sans_wildcard'] : 0;
            $wildcardLimit    = $wildcardIncluded + $wildcardBought;
        }
        else
        {
            $wildcardLimit = 0;
        }
        return [
            'single'   => $singleLimit,
            'wildcard' => $wildcardLimit,
            'singleFromOption' => $singleBought,
            'wildcardFromOption' => $wildcardBought,
            'singleIncluded' => $singleIncluded,
            'wildcardIncluded' => $wildcardIncluded,
        ];
    }

    private function validateAcmeDomain($domain, $domainType = 'single')
    {
        $domain = strtolower(trim((string)$domain));

        if($domainType === 'single' && strpos($domain, '*.') === 0)
        {
            throw new \Exception(Lang::absoluteT('single_domains_can_contain_only_regular_domains'));
        }

        if($domainType === 'wildcard')
        {
            if(strpos($domain, '*.') !== 0)
            {
                throw new \Exception(Lang::absoluteT('wildcard_domains_can_contain_only_wildcard_domains'));
            }
            $domain = substr($domain, 2);
        }

        if(Helpers::validateDomain($domain) !== true)
        {
            throw new \Exception(sprintf(Lang::absoluteT('invalid_domain'), $domain));
        }
    }

    private function getActiveAcmeDomainsCount($serviceId)
    {
        $activeDomains = AcmeSubscriptionDomain::where('service_id', $serviceId)->get();

        $single   = 0;
        $wildcard = 0;
        foreach($activeDomains as $domainRow)
        {
            if(isset($domainRow->domain_type) && strtolower((string)$domainRow->domain_type) === 'wildcard')
            {
                $wildcard++;
            }
            else
            {
                $single++;
            }
        }

        return [
            'single'   => $single,
            'wildcard' => $wildcard,
        ];
    }

    private function indexAcmeHTML($input, $vars = array())
    {
        $serviceId    = (int)$input['params']['serviceid'];
        $userId       = (int)$input['params']['userid'];
        $sslService   = SSL::getByServiceId($serviceId);
        $product      = Product::find((int)$input['params']['pid']);
        $subscription = AcmeSubscription::where('service_id', $serviceId)->first();

        if($subscription && (int)$subscription->api_order_id > 0 && strtolower((string)$subscription->status) !== 'active')
        {
            try
            {
                AcmeHelpers::refreshAcmeCertificateDetails($serviceId);
                $subscription = AcmeSubscription::where('service_id', $serviceId)->first();
            }
            catch(\Exception $e)
            {
                \logActivity('TSSGGS: Unable to auto-refresh ACME certificate details for service #' . (int)$serviceId . '. Error: ' . $e->getMessage());
            }
        }

        $domains = AcmeSubscriptionDomain::where('service_id', $serviceId)->get();

        $wildcardEnabled = Helpers::isWildcardEnabled($product->id);
        $limits          = $this->getAcmeDomainLimits($input['params']);
        //$singleBoughtSans       = isset($input['params']['configoptions']['sans']) ? (int)$input['params']['configoptions']['sans'] : 0;
        //$wildcardBoughtSans     = isset($input['params']['configoptions']['sans_wildcard']) ? (int)$input['params']['configoptions']['sans_wildcard'] : 0;
        $singleBoughtSans       = $limits['single'];
        $wildcardBoughtSans     = $limits['wildcard'];
        $activeCounts           = $this->getActiveAcmeDomainsCount($serviceId);
        $singleSansCurrent      = $activeCounts['single'];
        $wildcardSansCurrent    = $activeCounts['wildcard'];
        $totalSansCurrent       = $singleSansCurrent + $wildcardSansCurrent;
        $totalSansPurchased     = $singleBoughtSans + $wildcardBoughtSans;
        $availableSingleSlots   = max(0, $limits['single'] - $singleSansCurrent);
        $availableWildcardSlots = max(0, $limits['wildcard'] - $wildcardSansCurrent);
        $canAddDomains          = $availableSingleSlots > 0 || ($availableWildcardSlots > 0 && $wildcardEnabled);

        $vars['allOk']                  = true;
        $vars['serviceid']              = $serviceId;
        $vars['userid']                 = $userId;
        $vars['assetsURL']              = Server::I()->getAssetsURL();
        $vars['isAcmeSubscription']     = true;
        $vars['productName']            = isset($product->name) ? $product->name : 'Sectigo ACME Certificate-as-a-Service';
        $vars['allowWildcard']          = $wildcardEnabled;
        $vars['subscription']           = $subscription;
        $vars['domains']                = $domains;
        $vars['singleSansCurrent']      = $singleSansCurrent;
        $vars['singleSansPurchased']    = $singleBoughtSans;
        $vars['wildcardSansCurrent']    = $wildcardSansCurrent;
        $vars['wildcardSansPurchased']  = $wildcardBoughtSans;
        $vars['totalSansCurrent']       = $totalSansCurrent;
        $vars['totalSansPurchased']     = $totalSansPurchased;
        $vars['availableSingleSlots']   = $availableSingleSlots;
        $vars['availableWildcardSlots'] = $availableWildcardSlots;
        $vars['canAddDomains']          = $canAddDomains;
        $vars['configurationStatus']    = $sslService ? $sslService->status : null;
        $vars['configurationURL']       = 'clientarea.php?action=productdetails&id=' . $serviceId . '&acmeconfig=1';

        if($vars['configurationStatus'] === 'Awaiting Configuration')
        {
            return array(
                'tpl'  => 'home_acme_awaiting_configuration',
                'vars' => $vars
            );
        }

        return array(
            'tpl'  => 'home_acme',
            'vars' => $vars
        );
    }

    private function acmeConfigurationHTML($input, $vars = array())
    {
        $whmcsProductId = (int)$input['params']['packageid'];
        $serviceId      = (int)$input['params']['serviceid'];
        $userId         = (int)$input['params']['userid'];

        $product = Capsule::table('tblproducts')
                          ->select(['name', 'configoption13'])
                          ->where('id', (int)$input['params']['pid'])
                          ->first();

        $singleLimit       = (int)$input['params']['configoption7'];
        $singleOptionCount = isset($input['params']['configoptions']['sans']) ? (int)$input['params']['configoptions']['sans'] : 0;
        $singleLimit       += $singleOptionCount;

        $wildcardLimit       = (int)$input['params']['configoption8'];
        $wildcardOptionCount = isset($input['params']['configoptions']['sans_wildcard']) ? (int)$input['params']['configoptions']['sans_wildcard'] : 0;
        $wildcardLimit       += $wildcardOptionCount;

        $vars['allOk']                = true;
        $vars['serviceid']            = $serviceId;
        $vars['userid']               = $userId;
        $vars['assetsURL']            = Server::I()->getAssetsURL();
        $vars['isAcmeSubscription']   = true;
        $vars['productName']          = isset($product->name) ? $product->name : 'Sectigo ACME Certificate-as-a-Service';
        $vars['allowWildcard']        = Helpers::isWildcardEnabled($whmcsProductId);
        $vars['singleDomainsLimit']   = $singleLimit;
        $vars['wildcardDomainsLimit'] = $wildcardLimit;

        return array(
            'tpl'  => 'home_acme_configuration',
            'vars' => $vars
        );
    }

    public function createSubscriptionJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $whmcsProductId  = (int)$input['params']['packageid'];
        $serviceId       = (int)$input['id'];
        $singleDomains   = $this->extractDomainsFromInput(isset($input['single_domains']) ? $input['single_domains'] : '');
        $wildcardDomains = $this->extractDomainsFromInput(isset($input['wildcard_domains']) ? $input['wildcard_domains'] : '');
        $domainsWithType = [];

        if(empty($singleDomains) && empty($wildcardDomains))
        {
            $domains = $this->extractDomainsFromInput(isset($input['domains']) ? $input['domains'] : '');
            if(empty($domains))
            {
                throw new \Exception(Lang::absoluteT('please_provide_at_least_one_domain'));
            }

            foreach($domains as $domain)
            {
                if(strpos($domain, '*.') === 0)
                {
                    $domainsWithType[$domain] = 'wildcard';
                }
                else
                {
                    $domainsWithType[$domain] = 'single';
                }
            }
        }
        else
        {
            foreach($singleDomains as $domain)
            {
                $domainsWithType[$domain] = 'single';
            }
            foreach($wildcardDomains as $domain)
            {
                $domainsWithType[$domain] = 'wildcard';
            }
        }

        if(empty($domainsWithType))
        {
            throw new \Exception(Lang::absoluteT('please_provide_at_least_one_domain'));
        }

        $limits               = $this->getAcmeDomainLimits($input['params']);
        $singleDomainsCount   = 0;
        $wildcardDomainsCount = 0;
        foreach($domainsWithType as $type)
        {
            if($type === 'wildcard')
            {
                $wildcardDomainsCount++;
            }
            else
            {
                $singleDomainsCount++;
            }
        }

        if($singleDomainsCount > $limits['single'])
        {
            throw new \Exception(sprintf(Lang::absoluteT('exceeded_limit_of_single_domains'), $limits['single']));
        }
        if($wildcardDomainsCount > 0 && $wildcardDomainsCount > $limits['wildcard'])
        {
            throw new \Exception(sprintf(Lang::absoluteT('exceeded_limit_of_wildcard_domains'), $limits['wildcard']));
        }

        if(in_array('wildcard', $domainsWithType, true) && (!Helpers::isWildcardEnabled($whmcsProductId)))
        {
            throw new \Exception(Lang::absoluteT('wildcard_domains_are_not_enabled_for_this_product'));
        }

        $subscription = AcmeSubscription::where('service_id', $serviceId)->first();

        if($subscription && (int)$subscription->api_order_id > 0)
        {
            throw new \Exception(Lang::absoluteT('subscription_is_already_created'));
        }

        foreach($domainsWithType as $domain => $type)
        {
            $this->validateAcmeDomain($domain, $type);
        }

        $domains = array_keys($domainsWithType);

        $apis   = Helpers::getConfiguredApis();
        $vendor = $input['params']['configoption1'];
        $api    = $apis[$vendor];

        // remote product ids can be codes (TSS: sectigo_caas_dv), not only numbers
        $acmeProductId = !empty($input['params']['configoption2']) ? $input['params']['configoption2'] : AcmeHelpers::getProductIds()[0];
        $acmeProductId = is_numeric($acmeProductId) ? (int)$acmeProductId : $acmeProductId;
        $response      = $api->createAcmeSubscription([
                                                          'product_id' => $acmeProductId,
                                                          'domains'    => implode(',', $domains),
                                                      ]);

        $apiOrderId   = isset($response['order_id']) ? (int)$response['order_id'] : (isset($response['id']) ? (int)$response['id'] : 0);
        $apiItems     = isset($response['items']) && is_array($response['items']) ? $response['items'] : array();
        $apiFirstItem = isset($apiItems[0]) && is_array($apiItems[0]) ? $apiItems[0] : array();
        $acmeId       = isset($apiFirstItem['id']) ? (int)$apiFirstItem['id'] : (isset($response['item_id']) ? (int)$response['item_id'] : 0);

        if($apiOrderId <= 0)
        {
            throw new \Exception(Lang::absoluteT('acme_api_did_not_return_subscription_order_id'));
        }

        $subscription = AcmeSubscription::where('service_id', $serviceId)->first();

        if(!$subscription)
        {
            $subscription = new AcmeSubscription();
        }

        $subscription->service_id      = $serviceId;
        $subscription->client_id       = (int)$input['params']['userid'];
        $subscription->api_order_id    = $apiOrderId;
        $subscription->acme_id         = $acmeId;
        $subscription->status          = isset($response['status']) ? $response['status'] : 'active';
        $subscription->acme_account_id = isset($response['acme_account_id']) ? (string)$response['acme_account_id'] : '';
        $subscription->eab_kid         = isset($response['eab_kid']) ? (string)$response['eab_kid'] : '';
        $subscription->eab_hmac_key    = isset($response['eab_hmac_key']) ? (string)$response['eab_hmac_key'] : '';
        $subscription->server_url      = isset($response['server_url']) ? (string)$response['server_url'] : '';
        $subscription->period_start    = isset($response['begin_date']) ? (string)$response['begin_date'] : '';
        $subscription->period_end      = isset($response['end_date']) ? (string)$response['end_date'] : '';
        $subscription->renewal_date    = isset($response['renewal_date']) ? $response['renewal_date'] : (isset($response['end_date']) ? $response['end_date'] : null);
        $subscription->auto_renew      = 1;
        $subscription->save();

        foreach($domainsWithType as $domain => $type)
        {
            $now                             = date('Y-m-d H:i:s');
            $subscriptionDomain              = new AcmeSubscriptionDomain();
            $subscriptionDomain->service_id  = (int)$serviceId;
            $subscriptionDomain->domain      = $domain;
            $subscriptionDomain->domain_type = $type;
            $subscriptionDomain->status      = 'added';
            $subscriptionDomain->added_at    = $now;
            $subscriptionDomain->created_at  = $now;
            $subscriptionDomain->updated_at  = $now;
            $subscriptionDomain->save();
        }

        $sslOrder = SSL::getByServiceId($serviceId);

        if($sslOrder)
        {
            $sslOrder->setRemoteId($apiOrderId);
            $sslOrder->status = 'Configuration Submitted';
            $sslOrder->setConfigdataKey('subscription', true);
            $sslOrder->setSSLStatus($subscription->status);
            if(!empty($subscription->period_start))
            {
                $sslOrder->setConfigdataKey('begin_date', $subscription->period_start);
            }
            if(!empty($subscription->period_end))
            {
                $sslOrder->setConfigdataKey('end_date', $subscription->period_end);
            }
            if(!empty($subscription->renewal_date))
            {
                $sslOrder->setConfigdataKey('renewal_date', $subscription->renewal_date);
            }
            $sslOrder->save();
        }

        return [
            'success' => true,
            'message' => 'Subscription configuration submitted successfully.',
        ];
    }

    public function buyMoreDomainsJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $serviceId       = (int)$input['id'];
        $singleDomains   = $this->extractDomainsFromInput(isset($input['single_domains']) ? $input['single_domains'] : '');
        $wildcardDomains = $this->extractDomainsFromInput(isset($input['wildcard_domains']) ? $input['wildcard_domains'] : '');
        $domainsWithType = [];

        if(empty($singleDomains) && empty($wildcardDomains))
        {
            $domains    = $this->extractDomainsFromInput(isset($input['domains']) ? $input['domains'] : '');
            $domainType = isset($input['domain_type']) && $input['domain_type'] === 'wildcard' ? 'wildcard' : 'single';

            foreach($domains as $domain)
            {
                $domainsWithType[$domain] = $domainType;
            }
        }
        else
        {
            foreach($singleDomains as $domain)
            {
                $domainsWithType[$domain] = 'single';
            }
            foreach($wildcardDomains as $domain)
            {
                $domainsWithType[$domain] = 'wildcard';
            }
        }

        if(empty($domainsWithType))
        {
            throw new \Exception(Lang::absoluteT('please_provide_at_least_one_domain'));
        }

        $subscription = AcmeSubscription::where('service_id', $serviceId)->first();

        if(!$subscription || (int)$subscription->api_order_id <= 0)
        {
            throw new \Exception(Lang::absoluteT('subscription_was_not_created_yet'));
        }

        if(in_array('wildcard', $domainsWithType, true) && (!Helpers::isWildcardEnabled($input['params']['packageid'])))
        {
            throw new \Exception(Lang::absoluteT('wildcard_domains_are_not_enabled_for_this_product'));
        }

        $limits            = $this->getAcmeDomainLimits($input['params']);
        $activeCounts      = $this->getActiveAcmeDomainsCount($serviceId);
        $remainingSingle   = max(0, $limits['single'] - $activeCounts['single']);
        $remainingWildcard = max(0, $limits['wildcard'] - $activeCounts['wildcard']);

        $singleDomainsCount   = 0;
        $wildcardDomainsCount = 0;
        foreach($domainsWithType as $type)
        {
            if($type === 'wildcard')
            {
                $wildcardDomainsCount++;
            }
            else
            {
                $singleDomainsCount++;
            }
        }

        if($singleDomainsCount > $remainingSingle)
        {
            throw new \Exception(sprintf(Lang::absoluteT('exceeded_limit_of_single_domains'), $limits['single']));
        }

        if($wildcardDomainsCount > $remainingWildcard)
        {
            throw new \Exception(sprintf(Lang::absoluteT('exceeded_limit_of_wildcard_domains'), $limits['wildcard']));
        }

        foreach($domainsWithType as $domain => $type)
        {
            $exist = AcmeSubscriptionDomain::where('service_id', $serviceId)->where('domain', $domain)->exists();
            if($exist)
            {
                throw new \Exception(sprintf(Lang::absoluteT('domain_is_already_added'), $domain));
            }

            $this->validateAcmeDomain($domain, $type);
        }

        $api = Helpers::getApiByServiceId($serviceId);

        $api->addAcmeDomains((int)$subscription->acme_id, [
            'domains' => implode(',', array_keys($domainsWithType)),
        ]);

        foreach($domainsWithType as $domain => $type)
        {
            $now                             = date('Y-m-d H:i:s');
            $subscriptionDomain              = new AcmeSubscriptionDomain();
            $subscriptionDomain->service_id  = (int)$serviceId;
            $subscriptionDomain->domain      = $domain;
            $subscriptionDomain->domain_type = $type;
            $subscriptionDomain->status      = 'added';
            $subscriptionDomain->added_at    = $now;
            $subscriptionDomain->created_at  = $now;
            $subscriptionDomain->updated_at  = $now;
            $subscriptionDomain->save();
        }

        return [
            'success' => true,
            'message' => 'Domains added successfully.',
        ];
    }

    public function upgradeConfigOptionsJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $params    = $input['params'];
        $serviceId = (int)$input['id'];
        $service   = Service::find($serviceId);

        if(!$service)
        {
            throw new \Exception(Lang::absoluteT('acme_upgrade_service_not_found'));
        }

        $newSingle   = isset($input['new_sans_count']) ? (int)$input['new_sans_count'] : -1;
        $newWildcard = isset($input['new_sans_wildcard_count']) ? (int)$input['new_sans_wildcard_count'] : -1;

        if($newSingle < 0 && $newWildcard < 0)
        {
            throw new \Exception(Lang::absoluteT('acme_upgrade_no_value_provided'));
        }

        $domainLimits = $this->getAcmeDomainLimits($params);

        $newSingle   -= $domainLimits['singleIncluded'];
        $newWildcard -= $domainLimits['wildcardIncluded'];

        if($newSingle == $domainLimits['singleFromOption'] && $newWildcard == $domainLimits['wildcardFromOption'])
        {
            throw new \Exception(Lang::absoluteT('acme_upgrade_no_change_provided'));
        }


        // Block upgrade if any unpaid invoice exists for this service
        $unpaidInvoice = Capsule::table('tblinvoices')
                                ->join('tblinvoiceitems', 'tblinvoiceitems.invoiceid', '=', 'tblinvoices.id')
                                ->where('tblinvoiceitems.type', 'Hosting')
                                ->where('tblinvoiceitems.relid', $serviceId)
                                ->whereIn('tblinvoices.status', ['Unpaid', 'Overdue'])
                                ->first();

        if($unpaidInvoice)
        {
            throw new \Exception(Lang::absoluteT('acme_upgrade_unpaid_invoice_exists'));
        }

        $activeCounts         = $this->getActiveAcmeDomainsCount($serviceId);
        $configOptionsPayload = [];

        if($newSingle >= 0)
        {
            if($newSingle < ($activeCounts['single'] - $domainLimits['singleIncluded']))
            {
                throw new \Exception(sprintf(
                                         Lang::absoluteT('acme_downgrade_not_enough_single_slots'),
                                         $activeCounts['single']
                                     ));
            }

            $singleConfigId = ProductConfigOptions::getOptionIdBySystemName($service->packageid, 'sans');
            if(!$singleConfigId)
            {
                throw new \Exception(Lang::absoluteT('acme_upgrade_config_option_not_found'));
            }
            $configOptionsPayload[$singleConfigId] = $newSingle;
        }
        else
        {
            throw new \Exception(sprintf(
                                     Lang::absoluteT('acme_downgrade_not_enough_single_slots'),
                                     $activeCounts['single']
                                 ));
        }


        if($newWildcard >= 0)
        {
            if($newWildcard < ($activeCounts['wildcard'] - $domainLimits['wildcardIncluded']))
            {
                throw new \Exception(sprintf(
                                         Lang::absoluteT('acme_downgrade_not_enough_wildcard_slots'),
                                         $activeCounts['wildcard']
                                     ));
            }
            $wildcardConfigId = ProductConfigOptions::getOptionIdBySystemName($service->packageid, 'sans_wildcard');
            if(!$wildcardConfigId)
            {
                throw new \Exception(main\mgLibs\Lang::absoluteT('acme_upgrade_config_option_not_found'));
            }
            $configOptionsPayload[$wildcardConfigId] = $newWildcard;
        }
        else
        {
            throw new \Exception(sprintf(
                                     Lang::absoluteT('acme_downgrade_not_enough_wildcard_slots'),
                                     $activeCounts['wildcard']
                                 ));
        }

        $apiParams = [
            'serviceid'     => $serviceId,
            'paymentmethod' => $service->paymentmethod,
            'type'          => 'configoptions',
            'configoptions' => $configOptionsPayload,
        ];

        $result = localAPI('UpgradeProduct', $apiParams);

        if(!isset($result['result']) || $result['result'] !== 'success')
        {
            $errorMsg = isset($result['message']) ? $result['message'] : 'UpgradeProduct API failed';
            throw new \Exception($errorMsg);
        }

        return [
            'success' => true,
            'message' => Lang::absoluteT('acme_upgrade_success'),
        ];
    }

    public function removeDomainJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $serviceId = (int)$input['id'];
        $domain    = isset($input['domain']) ? strtolower(trim($input['domain'])) : '';
        if(empty($domain))
        {
            throw new \Exception(Lang::absoluteT('domain_is_required'));
        }

        $subscription = AcmeSubscription::getByServiceId($serviceId);

        if(!$subscription || (int)$subscription->api_order_id <= 0)
        {
            throw new \Exception(Lang::absoluteT('subscription_was_not_created_yet'));
        }

        $api = Helpers::getApiByServiceId($serviceId);
        $api->removeAcmeDomain((int)$subscription->acme_id, [
            'domain' => $domain,
        ]);

        AcmeSubscriptionDomain::markDomainAsRemoved($serviceId, $domain);

        $acmeSubscriptionDomain = AcmeSubscriptionDomain::where('service_id', $serviceId)
                                                        ->where('domain', $domain)
                                                        ->first();

        $addedAt      = $acmeSubscriptionDomain ? strtotime((string)$acmeSubscriptionDomain->added_at) : false;
        $withinWindow = $addedAt !== false && (time() - $addedAt) < (30 * 24 * 60 * 60);

        if($withinWindow)
        {
            // Added within 30 days — delete the row entirely so the slot is freed. No money is credited here; the text
            // (despite the key name) only says that the slot limit was reduced.
            $acmeSubscriptionDomain->delete();

            return [
                'success' => true,
                'message' => Lang::absoluteT('acme_domain_removed_and_refunded'),
            ];
        }

        return [
            'success' => true,
            'message' => Lang::absoluteT('acme_domain_removed_successfully'),
        ];
    }

    public function cancelSubscriptionJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $serviceId    = (int)$input['id'];
        $subscription = AcmeSubscription::getByServiceId($serviceId);

        if(!$subscription || (int)$subscription->acme_id <= 0)
        {
            throw new \Exception(Lang::absoluteT('subscription_was_not_created_yet'));
        }


        // acme_id is the CaaS item ID (items[0].id), not the order ID
        $api = Helpers::getApiByServiceId($serviceId);
        $api->cancelItem((int)$subscription->acme_id, 'Cancelled by client', 'caas');

        $subscription->status       = 'cancelled';
        $subscription->cancelled_at = date('Y-m-d H:i:s');
        $subscription->auto_renew   = 0;
        $subscription->save();

        Capsule::table('tblhosting')->where('id', $serviceId)->update([
                                                                          'domainstatus' => 'Cancelled'
                                                                      ]);

        $refundedCreditsAmount = $this->refundLatestPaidServiceInvoice($serviceId, 'ACME subscription cancelled within refund window');
        $message               = Lang::absoluteT('acme_subscription_cancelled');
        if($refundedCreditsAmount > 0)
        {
            $message = sprintf(
                Lang::absoluteT('acme_subscription_cancelled_and_credits_refunded'),
                number_format((float)$refundedCreditsAmount, 2, '.', '')
            );
        }

        return [
            'success' => true,
            'message' => $message,
        ];
    }

    public function stopAutoRenewalJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $serviceId    = (int)$input['id'];
        $subscription = AcmeSubscription::getByServiceId($serviceId);

        if(!$subscription || (int)$subscription->api_order_id <= 0)
        {
            throw new \Exception(Lang::absoluteT('subscription_was_not_created_yet'));
        }

        $api = Helpers::getApiByServiceId($serviceId);
        $api->disableSubscriptionAutoRenewal((int)$subscription->acme_id);
        $subscription->auto_renew = 0;
        $subscription->save();

        return [
            'success' => true,
            'message' => Lang::absoluteT('acme_auto_renewal_disabled'),
        ];
    }

    private function refundLatestPaidServiceInvoice($serviceId, $reason = '')
    {
        $invoice = $this->getLatestPaidServiceInvoiceForRefund($serviceId);

        if(!$invoice || empty($invoice->invoiceid))
        {
            return 0.00;
        }

        $creditPrefix          = 'ACME refund for invoice #' . (int)$invoice->invoiceid;
        // the prefix match must not catch the credit of another invoice (#12 vs #120): the number is followed by the end
        // of the text or by ". Reason: ..."
        $alreadyCreditedAmount = Capsule::table('tblcredit')
                                        ->where('clientid', (int)$invoice->userid)
                                        ->where(function($query) use ($creditPrefix) {
                                            $query->where('description', $creditPrefix)
                                                  ->orWhere('description', 'LIKE', $creditPrefix . '. %');
                                        })
                                        ->sum('amount');

        if((float)$alreadyCreditedAmount > 0)
        {
            return round((float)$alreadyCreditedAmount, 2);
        }

        $creditAmount = $this->getRefundableAcmeInvoiceAmount((int)$invoice->invoiceid, (int)$serviceId);
        if($creditAmount <= 0)
        {
            \logActivity(
                'TSSGGS: Skipped ACME refund credit for invoice #' . (int)$invoice->invoiceid
                . ' (service #' . (int)$serviceId . ') because refundable ACME items amount is 0.'
            );

            return 0.00;
        }

        $creditDescription = $creditPrefix;
        if(!empty($reason))
        {
            $creditDescription .= '. Reason: ' . $reason;
        }

        $result = localAPI('AddCredit', [
            'clientid'    => (int)$invoice->userid,
            'description' => $creditDescription,
            'amount'      => number_format($creditAmount, 2, '.', ''),
        ]);

        if(!isset($result['result']) || $result['result'] !== 'success')
        {
            \logActivity(
                'TSSGGS: Unable to add refund credit for invoice #' . (int)$invoice->invoiceid . ' (service #' . (int)$serviceId . ').'
            );
            return 0.00;
        }

        \logActivity(
            'TSSGGS: Credit refund added for invoice #' . (int)$invoice->invoiceid
            . ' (service #' . (int)$serviceId . ', amount: ' . number_format($creditAmount, 2, '.', '') . ').'
            . (!empty($reason) ? ' Reason: ' . $reason : '')
        );

        return round((float)$creditAmount, 2);
    }

    private function getLatestPaidServiceInvoiceForRefund($serviceId)
    {
        $baseQuery = Capsule::table('tblinvoiceitems')
                            ->select([
                                         'tblinvoiceitems.invoiceid',
                                         'tblinvoiceitems.description',
                                         'tblinvoices.userid',
                                         'tblinvoices.total',
                                         'tblinvoices.datepaid',
                                     ])
                            ->join('tblinvoices', 'tblinvoices.id', '=', 'tblinvoiceitems.invoiceid')
                            ->where('tblinvoices.datepaid', '>', date('Y-m-d H:i:s', strtotime('-30 days')))
                            ->where('tblinvoiceitems.relid', (int)$serviceId)
                            ->where('tblinvoiceitems.type', 'Hosting')
                            ->where('tblinvoices.status', 'Paid');

        $renewalInvoice = (clone $baseQuery)
            ->where(function($query) {
                $query->where('tblinvoiceitems.description', 'LIKE', '%ACME Subscription Renewal%');
            })
            ->orderBy('tblinvoices.datepaid', 'DESC')
            ->first();

        if($renewalInvoice)
        {
            return $renewalInvoice;
        }

        return (clone $baseQuery)
            ->orderBy('tblinvoices.datepaid', 'DESC')
            ->first();
    }

    private function getRefundableAcmeInvoiceAmount($invoiceId, $serviceId)
    {
        $acmeProductIds = AcmeHelpers::getProductIds();

        if(empty($acmeProductIds))
        {
            return 0.00;
        }

        $invoice     = Invoice::find($invoiceId);
        $invoiceItem = $invoice->items()
                               ->join('tblhosting', 'tblhosting.id', '=', 'tblinvoiceitems.relid')
                               ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
                               ->where('tblinvoiceitems.invoiceid', (int)$invoiceId)
                               ->where('tblinvoiceitems.type', 'Hosting')
                               ->where('tblinvoiceitems.relid', (int)$serviceId)
                               ->where('tblproducts.servertype', 'TSSGGSModule')
                               ->whereIn('tblproducts.configoption2', $acmeProductIds)
                               ->select(['tblinvoiceitems.*'])
                               ->first();

        $itemAmount     = $invoiceItem ? $invoiceItem->amount : 0.00;
        $clientsDetails = getClientsDetails($invoice->userid);
        $taxEnabled     = \WHMCS\Config\Setting::getValue("TaxEnabled");

        if($invoiceItem->taxed && $taxEnabled && !$clientsDetails["taxexempt"])
        {
            $taxRate  = $invoice->taxRate1;
            $taxRate2 = $invoice->taxRate2;

            if(round($taxRate, 2) == $taxRate)
            {
                $taxRate = \format_as_currency($taxRate);
            }
            if(round($taxRate2, 2) == $taxRate2)
            {
                $taxRate2 = \format_as_currency($taxRate2);
            }

            $taxCalculator = new Tax();
            $taxCalculator->setIsInclusive(\WHMCS\Config\Setting::getValue("TaxType") == "Inclusive")->setIsCompound(\WHMCS\Config\Setting::getValue("TaxL2Compound"));

            if(is_numeric($taxRate))
            {
                $taxCalculator->setLevel1Percentage($taxRate);
            }
            if(is_numeric($taxRate2))
            {
                $taxCalculator->setLevel2Percentage($taxRate2);
            }

            $tax = $tax2 = 0;

            $taxCalculator->setTaxBase($invoiceItem->amount);
            $tax          += $taxCalculator->getLevel1TaxTotal();
            $tax2         += $taxCalculator->getLevel2TaxTotal();
            $itemSubtotal = $taxCalculator->getTotalBeforeTaxes();
            $itemAmount   = $itemSubtotal + $tax + $tax2;
        }

        return round((float)$itemAmount, 2);
    }

    public function checkCertificateDetailsJSON($input, $vars = array())
    {
        $serviceId = (int)$input['id'];

        AcmeHelpers::refreshAcmeCertificateDetails($serviceId);

        return [
            'success' => true,
            'message' => Lang::absoluteT('certificate_details_refreshed'),
        ];
    }

    /* ---------------- S/MIME (GoGetSSL, /certificates/smime) ---------------- */

    /**
     * S/MIME service page: the order form while the service waits for configuration, else the order status
     */
    private function indexSmimeHTML($input, $vars = array())
    {
        $serviceId = (int)$input['params']['serviceid'];
        $ssl       = SSL::getByServiceId($serviceId);
        if(!$ssl)
        {
            throw new \Exception(Lang::absoluteT('smimeNotInitialized'));
        }

        $vars = $this->smimeBaseVars($input, $vars);

        if($ssl->status === 'Awaiting Configuration' && !SmimeService::isOrdered($serviceId))
        {
            return $this->smimeConfigurationHTML($input, $vars);
        }

        $stored = SmimeService::stored($serviceId);
        $vars['refreshError'] = '';
        if(SmimeService::refreshDue($stored))
        {
            try
            {
                $stored = SmimeService::refresh($serviceId);
            }
            catch(\Exception $e)
            {
                $vars['refreshError'] = $e->getMessage();
            }
        }

        $status = strtolower((string)($stored['status'] ?? 'processing'));
        $emails = (array)($stored['emails'] ?? []);
        $tones  = ['active' => 'ok', 'processing' => 'wait', 'pending' => 'wait', 'unpaid' => 'warn', 'incomplete' => 'warn',
                   'failed' => 'bad', 'rejected' => 'bad', 'cancelled' => 'off', 'revoked' => 'off', 'refunded' => 'off', 'expired' => 'off'];

        $vars['smimeStatus']        = $status;
        $vars['smimeStatusText']    = $this->smimeStatusText($status);
        $vars['smimeTone']          = $tones[$status] ?? 'wait';
        $vars['smimeEmails']        = $emails;
        $vars['smimeSubject']       = $emails ? $emails[0]['email'] : (string)Capsule::table('tblhosting')->where('id', $serviceId)->value('domain');
        $vars['smimeAllApproved']   = $emails && !array_filter($emails, function($e) { return empty($e['approved']); });
        $vars['smimeOrderId']       = (int)($stored['orderId'] ?? 0);
        $vars['smimeItemId']        = (int)($stored['itemId'] ?? 0);
        $vars['smimeVendorOrderId'] = (string)($stored['vendorOrderId'] ?? '');
        $vars['smimeValidFrom']     = $this->smimeDate($stored['validFrom'] ?? '');
        $vars['smimeValidTill']     = $this->smimeDate($stored['validTill'] ?? '');
        $vars['smimeOrderedAt']     = $this->smimeDate($stored['orderedAt'] ?? '');
        $vars['smimeCsrProvided']   = !empty($stored['csrProvided']);

        return ['tpl' => 'home_smime', 'vars' => $vars];
    }

    private function smimeConfigurationHTML($input, $vars)
    {
        $serviceId = (int)$input['params']['serviceid'];
        $client    = Capsule::table('tblclients')->where('id', (int)$input['params']['userid'])->first();

        $phone = $client ? trim((string)$client->phonenumber) : '';
        if($phone !== '' && strpos($phone, '+') !== 0 && !empty($client->phonecc))
        {
            $phone = '+' . ltrim((string)$client->phonecc, '+') . '.' . $phone;
        }

        $previous = (array)(SmimeService::stored($serviceId)['previous'] ?? []);
        $last     = $previous ? (array)end($previous) : [];
        $lastTill = !empty($last['validTill']) ? strtotime($last['validTill']) : false;

        $vars['smimeRequiresOrganization'] = SmimeService::requiresOrganization((int)$input['params']['pid']);
        $vars['smimeRegionRequired']       = in_array('region', SmimeService::organizationRequired((int)$input['params']['pid']), true);
        $vars['smimeMaxEmails']            = SmimeService::MAX_EMAILS;
        $vars['smimeRenewal']              = (bool)$previous;
        // renewal: the certificate ordered before stays valid until its end date
        $vars['smimeCurrentValidTill']     = $lastTill && $lastTill > time() && ($last['status'] ?? '') === 'active' ? date('F j, Y', $lastTill) : '';
        $vars['smimePreviousEmails']       = array_values((array)($last['emails'] ?? []));
        $vars['agreement']                 = CertificateService::agreement((int)$input['params']['pid']);
        $vars['smimeClient']               = [
            'email'       => $client ? $client->email : '',
            'firstname'   => $client ? $client->firstname : '',
            'lastname'    => $client ? $client->lastname : '',
            'companyname' => $client ? $client->companyname : '',
            'phonenumber' => $phone,
            'country'     => $client ? $client->country : '',
            'state'       => ($client && strcasecmp(trim((string)$client->state), 'None') !== 0) ? $client->state : '',
            'city'        => $client ? $client->city : '',
            'postcode'    => $client ? $client->postcode : '',
            'address1'    => $client ? $client->address1 : '',
            'address2'    => $client ? $client->address2 : '',
        ];
        $vars['smimeCountries'] = [];
        try
        {
            $vars['smimeCountries'] = (new \WHMCS\Utility\Country())->getCountryNameArray();
        }
        catch(\Throwable $e)
        {
        }

        return ['tpl' => 'home_smime_configuration', 'vars' => $vars];
    }

    public function createSmimeJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $serviceId = (int)$input['params']['serviceid'];
        if(!SmimeService::isSmimeWhmcsProduct((int)$input['params']['pid']))
        {
            throw new \Exception(Lang::absoluteT('smimeNotSmimeProduct'));
        }
        if(($input['params']['status'] ?? '') !== 'Active')
        {
            throw new \Exception(Lang::absoluteT('smimeServiceNotActive'));
        }

        $emails = SmimeService::parseEmails([(string)($input['smime_email'] ?? ''), (string)($input['smime_additional_emails'] ?? '')], $invalid);
        if($invalid)
        {
            throw new \Exception(sprintf(Lang::absoluteT('smimeInvalidEmail'), htmlspecialchars($invalid[0])));
        }
        if(!$emails)
        {
            throw new \Exception(Lang::absoluteT('smimeEmailRequired'));
        }
        if(count($emails) > SmimeService::MAX_EMAILS)
        {
            throw new \Exception(sprintf(Lang::absoluteT('smimeTooManyEmails'), SmimeService::MAX_EMAILS));
        }

        if(CertificateService::agreement((int)$input['params']['pid']) && empty($input['agreement']))
        {
            throw new \Exception(Lang::absoluteT('serverCA', 'home', 'sslConfigAgreementRequired'));
        }

        $csr = trim((string)($input['smime_csr'] ?? ''));
        if($csr !== '' && !@openssl_csr_get_subject($csr))
        {
            throw new \Exception(Lang::absoluteT('smimeInvalidCsr'));
        }

        $order = ['emails' => $emails, 'csr' => $csr];
        if(SmimeService::requiresOrganization((int)$input['params']['pid']))
        {
            $contacts = SmimeService::contacts((array)($input['org'] ?? []), (array)($input['admin'] ?? []), $missing, SmimeService::organizationRequired((int)$input['params']['pid']));
            if($missing)
            {
                throw new \Exception(sprintf(Lang::absoluteT('smimeFieldRequired'), Lang::absoluteT('serverCA', 'home', ($missing[0] === 'org' ? 'smimeOrg_' : 'smimeAdmin_') . $missing[1])));
            }
            if(filter_var($contacts['administrator']['email'], FILTER_VALIDATE_EMAIL) === false)
            {
                throw new \Exception(sprintf(Lang::absoluteT('smimeInvalidEmail'), htmlspecialchars($contacts['administrator']['email'])));
            }
            $order += $contacts;
        }

        // a double submit must not place two orders
        if(!$this->acquireServiceLock($serviceId))
        {
            throw new \Exception(Lang::absoluteT('smimeAlreadyOrdered'));
        }
        try
        {
            SmimeService::create($serviceId, $order);
        }
        finally
        {
            $this->releaseServiceLock($serviceId);
        }

        return ['success' => true, 'message' => Lang::absoluteT('smimeOrderedSuccessfully')];
    }

    public function refreshSmimeDetailsJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $serviceId = (int)$input['params']['serviceid'];
        if(!SmimeService::isOrdered($serviceId))
        {
            throw new \Exception(Lang::absoluteT('smimeNotOrderedYet'));
        }
        SmimeService::refresh($serviceId);

        return ['success' => true, 'message' => Lang::absoluteT('smimeDetailsRefreshed')];
    }

    private function smimeBaseVars($input, $vars)
    {
        $serviceId = (int)$input['params']['serviceid'];
        $service   = Capsule::table('tblhosting')->where('id', $serviceId)->first();
        $product   = Product::find((int)$input['params']['pid']);
        $currency  = $service ? \getCurrency((int)$service->userid) : null;

        $vars['allOk']       = true;
        $vars['serviceid']   = $serviceId;
        $vars['assetsURL']   = Server::I()->getAssetsURL();
        $vars['csrfToken']   = \generate_token('plain');
        $vars['productName'] = $product ? $product->name : '';
        $vars['billing']     = $service ? [
            'registration' => $this->smimeDate($service->regdate),
            'nextDue'      => $this->smimeDate($service->nextduedate),
            'amount'       => \formatCurrency($service->amount, $currency ? $currency['id'] : null),
            'cycle'        => $service->billingcycle,
        ] : [];

        return $vars;
    }

    private function smimeStatusText($status)
    {
        $text = Lang::absoluteT('serverCA', 'home', 'smimeStatus_' . $status);

        return (!is_string($text) || $text === '' || strpos($text, 'smimeStatus_') !== false) ? ucfirst($status) : $text;
    }

    /** 'November 6, 2026' (the client area date format of the module) */
    private function smimeDate($value)
    {
        $time = $value && strpos((string)$value, '0000-00-00') !== 0 ? strtotime((string)$value) : false;

        return $time ? date('F j, Y', $time) : '';
    }

    private function indexAisHTML($input, $vars = array())
    {
        $serviceId    = (int)$input['params']['serviceid'];
        $userId       = (int)$input['params']['userid'];
        $sslService   = SSL::getByServiceId($serviceId);
        $product      = Product::find((int)$input['params']['pid']);
        $subscription = AisSubscription::getByServiceId($serviceId);

        if($subscription && (int)$subscription->api_order_id > 0 && strtolower((string)$subscription->status) !== 'active')
        {
            try
            {
                AisHelpers::refreshAisDetails($serviceId);
                $subscription = AisSubscription::getByServiceId($serviceId);
            }
            catch(\Exception $e)
            {
                \logActivity('TSSGGS: Unable to auto-refresh AIS details for service #' . $serviceId . '. Error: ' . $e->getMessage());
            }
        }

        $vars['allOk']              = true;
        $vars['serviceid']          = $serviceId;
        $vars['userid']             = $userId;
        $vars['assetsURL']          = Server::I()->getAssetsURL();
        $vars['isAisSubscription']  = true;
        $vars['productName']        = isset($product->name) ? $product->name : '';
        $vars['subscription']       = $subscription;
        $vars['aisAutoRenew']       = $subscription && isset($subscription->auto_renew) ? (int)$subscription->auto_renew : 1;
        $vars['configurationStatus'] = $sslService ? $sslService->status : null;
        $vars['configurationURL']   = 'clientarea.php?action=productdetails&id=' . $serviceId . '&aisconfig=1';

        if($vars['configurationStatus'] === 'Awaiting Configuration')
        {
            return ['tpl' => 'home_ais_awaiting_configuration', 'vars' => $vars];
        }

        return ['tpl' => 'home_ais', 'vars' => $vars];
    }

    private function aisConfigurationHTML($input, $vars = array())
    {
        $serviceId = (int)$input['params']['serviceid'];
        $userId    = (int)$input['params']['userid'];
        $product   = Product::find((int)$input['params']['pid']);
        $service   = \WHMCS\Service\Service::find($serviceId);

        $vars['allOk']             = true;
        $vars['serviceid']         = $serviceId;
        $vars['userid']            = $userId;
        $vars['assetsURL']         = Server::I()->getAssetsURL();
        $vars['isAisSubscription'] = true;
        $vars['productName']       = isset($product->name) ? $product->name : '';
        $vars['serviceDomain']     = $service ? $service->domain : '';

        return ['tpl' => 'home_ais_configuration', 'vars' => $vars];
    }

    public function createAisJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $serviceId = (int)$input['id'];
        $domain    = isset($input['domain']) ? strtolower(trim($input['domain'])) : '';

        if(empty($domain))
        {
            throw new \Exception(Lang::absoluteT('aisDomainRequired'));
        }

        $subscription = AisSubscription::getByServiceId($serviceId);
        if($subscription && (int)$subscription->api_order_id > 0)
        {
            throw new \Exception(Lang::absoluteT('aisAlreadyCreated'));
        }

        $apis   = Helpers::getConfiguredApis();
        $vendor = $input['params']['configoption1'];
        $api    = $apis[$vendor];

        $client = \WHMCS\Service\Service::find($serviceId);

        $whmcsClient = Capsule::table('tblclients')
                              ->select(['email'])
                              ->where('id', (int)$input['params']['userid'])
                              ->first();

        $clientEmail  = $whmcsClient ? $whmcsClient->email : '';
        $apiProductId = isset($input['params']['configoption2']) ? (int)$input['params']['configoption2'] : 0;

        if($apiProductId <= 0)
        {
            throw new \Exception(Lang::absoluteT('aisMissingProductId'));
        }

        $response = $api->createAis([
            'product'  => ['id' => $apiProductId],
            'domains'  => [$domain],
            'contacts' => [
                'primary'   => ['email' => $clientEmail],
                'technical' => ['email' => $clientEmail],
            ],
        ]);

        $apiOrderId  = isset($response['order']['id'])     ? (int)$response['order']['id']     : 0;
        $orderStatus = isset($response['order']['status']) ? $response['order']['status']       : 'pending';
        $items       = isset($response['items']) && is_array($response['items']) ? $response['items'] : [];
        $firstItem   = isset($items[0]) ? (array)$items[0] : [];
        $aisItemId   = isset($firstItem['id']) ? (int)$firstItem['id'] : 0;

        if($apiOrderId <= 0)
        {
            throw new \Exception(Lang::absoluteT('aisMissingOrderId'));
        }

        if(!$subscription)
        {
            $subscription = new AisSubscription();
        }

        $subscription->service_id   = $serviceId;
        $subscription->client_id    = (int)$input['params']['userid'];
        $subscription->api_order_id = $apiOrderId;
        $subscription->ais_item_id  = $aisItemId > 0 ? $aisItemId : null;
        $subscription->status       = $orderStatus;
        $subscription->save();

        Capsule::table('tblhosting')->where('id', $serviceId)->update(['domain' => $domain]);

        $sslOrder = SSL::getByServiceId($serviceId);
        if($sslOrder)
        {
            $sslOrder->setRemoteId($apiOrderId);
            $sslOrder->status = 'Configuration Submitted';
            $sslOrder->setSSLStatus($orderStatus);
            $sslOrder->save();
        }

        if($aisItemId > 0)
        {
            try
            {
                AisHelpers::refreshAisDetails($serviceId);
            }
            catch(\Exception $e)
            {
                // Non-fatal — links fetched on next page load
            }
        }

        return ['success' => true, 'message' => Lang::absoluteT('aisCreatedSuccessfully')];
    }

    public function refreshAisDetailsJSON($input, $vars = array())
    {
        $serviceId = (int)$input['id'];
        AisHelpers::refreshAisDetails($serviceId);

        return ['success' => true, 'message' => Lang::absoluteT('aisDetailsRefreshed')];
    }

    public function stopAisAutoRenewalJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $serviceId    = (int)$input['id'];
        $subscription = AisSubscription::getByServiceId($serviceId);

        if(!$subscription || (int)$subscription->api_order_id <= 0)
        {
            throw new \Exception(Lang::absoluteT('aisSubscriptionNotCreatedYet'));
        }

        $api = Helpers::getApiByServiceId($serviceId);
        if((int)$subscription->ais_item_id > 0)
        {
            $api->disableAisAutoRenewal((int)$subscription->ais_item_id);
        }

        $subscription->auto_renew = 0;
        $subscription->save();

        return ['success' => true, 'message' => Lang::absoluteT('aisAutoRenewalDisabled')];
    }

    public function cancelAisSubscriptionJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $serviceId    = (int)$input['id'];
        $subscription = AisSubscription::getByServiceId($serviceId);

        if(!$subscription || (int)$subscription->api_order_id <= 0)
        {
            throw new \Exception(Lang::absoluteT('aisSubscriptionNotCreatedYet'));
        }

        $api = Helpers::getApiByServiceId($serviceId);
        if((int)$subscription->ais_item_id > 0)
        {
            $api->cancelAisSubscription((int)$subscription->ais_item_id, 'Cancelled by client');
        }

        $subscription->status       = 'cancelled';
        $subscription->auto_renew   = 0;
        $subscription->cancelled_at = date('Y-m-d H:i:s');
        $subscription->save();

        Capsule::table('tblhosting')->where('id', $serviceId)->update(['domainstatus' => 'Cancelled']);

        return ['success' => true, 'message' => Lang::absoluteT('aisSubscriptionCancelled')];
    }

    private function getSslApiForProduct($packageId)
    {
        $configProduct = (new ProductRepository())->getProductConfiguration($packageId);
        $addonConfig   = (new AddonModuleRepository())->getModuleConfiguration();

        $provider    = strtolower($configProduct['provider']);
        $credentials = $addonConfig['credentials'][$provider];

        if($provider == 'tss' && $credentials['OperationMode'] == 'sandbox')
        {
            $credentials['PartnerCode'] = $credentials['TestPartnerCode'];
            $credentials['AuthToken']   = $credentials['TestAuthToken'];
        }

        return new SSLTrustCenterApi($configProduct['provider'], $credentials['PartnerCode'], $credentials['AuthToken'], $configProduct['category']);
    }

    private function getSslConfigurationSettings($params)
    {
        $configProduct = (new ProductRepository())->getProductConfiguration($params['packageid']);
        $addonConfig   = (new AddonModuleRepository())->getModuleConfiguration();
        $sslSettings   = isset($addonConfig['sslSettings']) && is_array($addonConfig['sslSettings']) ? $addonConfig['sslSettings'] : [];

        $product    = Product::where('id', $params['packageid'])->first();
        $isWildcard = SslProductHelpers::isWildcardProduct($configProduct['product_id'], $product ? $product->name : '');

        $validation = strtoupper(trim((string)$configProduct['validation']));
        if(!in_array($validation, ['DV', 'OV', 'EV']))
        {
            $validation = 'DV';
        }

        $dcvMethods = array_values(array_filter(array_map('trim', explode(',', strtolower((string)$configProduct['dcv'])))));

        // Manual orders can pick the DNS record type (CNAME / TXT) when the CA offers both; AutoInstall creates
        // the record the CA asks for, so it keeps the single DNS method
        $manualMethods = array_values(array_intersect(['email', 'http', 'https', 'dns-cname', 'dns-txt'], $dcvMethods));
        if(!array_intersect(['dns-cname', 'dns-txt'], $manualMethods) && in_array('dns', $dcvMethods, true))
        {
            $manualMethods[] = 'dns';
        }
        if(!empty($sslSettings['disableEmailValidation']))
        {
            $manualMethods = array_values(array_diff($manualMethods, ['email']));
        }
        if($isWildcard)
        {
            $manualMethods = array_values(array_diff($manualMethods, ['http', 'https']));
        }

        // Same methods as in SSLCENTERWHMCS: dns-cname / dns-txt from the API product are shown as one DNS method
        $dcvMethods = array_map(function($method) {
            return in_array($method, ['dns-cname', 'dns-txt']) ? 'dns' : $method;
        }, $dcvMethods);
        $dcvMethods = array_values(array_intersect(['email', 'http', 'https', 'dns'], $dcvMethods));
        if(empty($dcvMethods))
        {
            $dcvMethods = ['email', 'http', 'https', 'dns'];
        }
        if(!empty($sslSettings['disableEmailValidation']))
        {
            $dcvMethods = array_values(array_diff($dcvMethods, ['email']));
        }
        if($isWildcard)
        {
            $dcvMethods = array_values(array_diff($dcvMethods, ['http', 'https']));
        }

        $sansLimit         = (int)$params['configoption7'] + (isset($params['configoptions']['sans']) ? (int)$params['configoptions']['sans'] : 0);
        $wildcardSansLimit = (int)$params['configoption8'] + (isset($params['configoptions']['sans_wildcard']) ? (int)$params['configoptions']['sans_wildcard'] : 0);

        return [
            'dcvMethodsManual'   => $manualMethods ?: $dcvMethods,
            'productName'        => $product ? $product->name : 'SSL Certificate',
            'isWildcard'         => $isWildcard,
            'validation'         => $validation,
            'dcvMethods'         => $dcvMethods,
            'sansLimit'          => $sansLimit,
            'wildcardSansLimit'  => $wildcardSansLimit,
            'enableCsrGenerator' => !empty($sslSettings['enableCsrGenerator']),
            'useProfileForCsr'   => !empty($sslSettings['useProfileDetailsForCsr']),
            'defaultCsrCountry'  => isset($sslSettings['defaultCsrCountry']) ? (string)$sslSettings['defaultCsrCountry'] : '',
            'adminFormHidden'    => !empty($sslSettings['autoDetailsForDvOrders']) && $validation === 'DV',
        ];
    }

    private function getCountriesList()
    {
        $file      = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__DIR__))))))) . DS . 'resources' . DS . 'country' . DS . 'dist.countries.json';
        $countries = [];
        if(file_exists($file))
        {
            $decoded = json_decode(file_get_contents($file), true);
            if(is_array($decoded))
            {
                foreach($decoded as $code => $country)
                {
                    $countries[$code] = isset($country['name']) ? $country['name'] : $code;
                }
            }
        }
        asort($countries);

        return $countries;
    }

    private function sslConfigurationHTML($input, $vars = array())
    {
        $params    = $input['params'];
        $serviceId = (int)$params['serviceid'];
        $userId    = (int)$params['userid'];
        $settings  = $this->getSslConfigurationSettings($params);

        // All client's cPanel accounts - null means manual enrollment only
        $cpanelDomains = null;
        try
        {
            // the lists come from a short-lived cache; "refresh the list" (refreshdomains=1) asks the servers live
            $cpanelDomains = Cpanel::getMergedDomainsGrouped($userId, !empty($_GET['refreshdomains']));
        }
        catch(\Throwable $e)
        {
        }

        // Each parent domain is an optgroup with itself + its subdomains
        $domainGroups = [];
        if($cpanelDomains !== null)
        {
            $parents = [];
            if(!empty($cpanelDomains['main']))
            {
                $parents[] = $cpanelDomains['main'];
            }
            foreach(array_merge($cpanelDomains['addon'], $cpanelDomains['parked']) as $d)
            {
                $parents[] = $d;
            }
            foreach($parents as $parent)
            {
                $group = ['label' => $parent, 'domains' => [$parent]];
                foreach($cpanelDomains['subdomain'] as $sub)
                {
                    if($sub['parent'] === $parent)
                    {
                        $group['domains'][] = $sub['domain'];
                    }
                }
                $domainGroups[] = $group;
            }
        }

        $countries      = $this->getCountriesList();
        $defaultCountry = $settings['defaultCsrCountry'];
        $client         = Client::where('id', $userId)->first();

        // no default country in the settings: the client's own country, not the first one of the list
        if((!$defaultCountry || !isset($countries[strtoupper((string)$defaultCountry)])) && $client && isset($countries[strtoupper((string)$client->country)]))
        {
            $defaultCountry = strtoupper((string)$client->country);
        }

        $csrDefaults = ['country' => $defaultCountry, 'state' => '', 'city' => '', 'org' => '', 'email' => ''];
        if($settings['useProfileForCsr'] && $client)
        {
            $clientCountry = strtoupper(trim((string)$client->country));
            if($clientCountry && isset($countries[$clientCountry]))
            {
                $csrDefaults['country'] = $clientCountry;
            }
            $csrDefaults['state'] = (string)$client->state;
            $csrDefaults['city']  = (string)$client->city;
            $csrDefaults['org']   = (string)$client->companyname;
            $csrDefaults['email'] = (string)$client->email;
        }

        $vars['allOk']              = true;
        $vars['serviceid']          = $serviceId;
        $vars['assetsURL']          = Server::I()->getAssetsURL();
        $vars['productName']        = $settings['productName'];
        $vars['domainGroups']       = $domainGroups;
        $vars['hasCpanel']          = !empty($domainGroups);
        $vars['dcvMethods']         = $settings['dcvMethods'];
        $vars['dcvMethodsManual']   = $settings['dcvMethodsManual'];
        $vars['countries']          = $countries;
        $vars['csrDefaults']        = $csrDefaults;
        $vars['client']             = $client;
        $vars['clientCountry']      = $client ? strtoupper((string)$client->country) : $defaultCountry;
        $vars['sansLimit']          = $settings['sansLimit'];
        $vars['wildcardSansLimit']  = $settings['wildcardSansLimit'];
        $vars['isMultiDomain']      = $settings['sansLimit'] > 0 || $settings['wildcardSansLimit'] > 0;
        $vars['isWildcard']         = $settings['isWildcard'];
        $vars['certTypeConfig']     = $settings['validation'] === 'DV' ? 'DV' : 'OV';
        $vars['enableCsrGenerator'] = $settings['enableCsrGenerator'];
        $vars['adminFormHidden']    = $settings['adminFormHidden'];
        $vars['productDomain']      = isset($params['domain']) ? $params['domain'] : '';
        $vars['csrfToken']          = \generate_token('plain');
        $vars['agreement']          = CertificateService::agreement($params['packageid']);

        return [
            'tpl'  => 'home_ssl_configuration',
            'vars' => $vars,
        ];
    }

    public function getApproverEmailsJSON($input, $vars = array())
    {
        $domain = isset($input['domain']) ? strtolower(trim($input['domain'])) : '';
        if(strpos($domain, '*.') === 0)
        {
            $domain = substr($domain, 2);
        }
        if(empty($domain))
        {
            return ['emails' => []];
        }

        try
        {
            $api    = $this->getSslApiForProduct($input['params']['packageid']);
            $result = $api->getDomainEmails([$domain]);
            $emails = [];
            foreach((array)$result as $row)
            {
                if(isset($row['emails']) && is_array($row['emails']))
                {
                    foreach($row['emails'] as $email)
                    {
                        if(is_string($email) && !empty($email))
                        {
                            $emails[] = $email;
                        }
                    }
                }
            }
            return ['emails' => array_values(array_unique($emails))];
        }
        catch(\Exception $e)
        {
            return ['emails' => []];
        }
    }

    private function parseSslDomainList($raw)
    {
        if(is_array($raw))
        {
            $raw = implode("\n", $raw);
        }
        $items = preg_split('/[\s,;]+/', trim((string)$raw));

        // Unicode names are ordered in their punycode form
        return array_values(array_unique(array_filter(array_map(function($item) {
            return strtolower(Domain::toAscii(trim($item)));
        }, $items))));
    }

    /** The gateway product accepts IP addresses as SANs (san.ip_allowed) */
    private function sanIpAllowed($packageId)
    {
        $config = (new ProductRepository())->getProductConfiguration((int)$packageId);
        $remote = RemoteProduct::where('remoteId', (string)($config['product_id'] ?? ''))->where('vendor', (string)($config['provider'] ?? ''))->first();
        $raw    = $remote ? $remote->rawData : [];
        $raw    = is_array($raw) ? $raw : (json_decode((string)$raw, true) ?: []);

        return !empty($raw['san']['ip_allowed']);
    }

    public function submitSslConfigurationJSON($input, $vars = array())
    {
        $this->requireCsrfToken();

        $serviceId = (int)$input['params']['serviceid'];
        if(!$this->acquireServiceLock($serviceId))
        {
            throw new \Exception(Lang::absoluteT('sslConfigInProgress'));
        }

        try
        {
            return $this->doSubmitSslConfiguration($input);
        }
        finally
        {
            $this->releaseServiceLock($serviceId);
        }
    }

    private function doSubmitSslConfiguration($input)
    {
        $params    = $input['params'];
        $serviceId = (int)$params['serviceid'];
        $userId    = (int)$params['userid'];

        $ssl = SSL::getByServiceId($serviceId);
        if(!$ssl || $ssl->status !== 'Awaiting Configuration' || !empty($ssl->remoteid))
        {
            throw new \Exception(Lang::absoluteT('sslConfigAlreadySubmitted'));
        }

        if(CertificateService::agreement($params['packageid']) && empty($input['agreement']))
        {
            throw new \Exception(Lang::absoluteT('serverCA', 'home', 'sslConfigAgreementRequired'));
        }

        $settings    = $this->getSslConfigurationSettings($params);
        $installType = (isset($input['install_type']) && $input['install_type'] === 'autoinstall') ? 'autoinstall' : 'manual';
        $dcvMethod   = isset($input['dcv_method']) ? strtolower(trim($input['dcv_method'])) : '';
        $includeWww  = !empty($input['include_www']);
        $ownCsr      = ($installType === 'manual') ? trim(isset($input['own_csr']) ? $input['own_csr'] : '') : '';

        if(!in_array($dcvMethod, $installType === 'manual' ? $settings['dcvMethodsManual'] : $settings['dcvMethods'], true))
        {
            throw new \Exception(Lang::absoluteT('sslConfigInvalidDcvMethod'));
        }
        if($installType === 'manual' && empty($ownCsr) && !$settings['enableCsrGenerator'])
        {
            throw new \Exception(Lang::absoluteT('sslConfigOwnCsrRequired'));
        }

        // ---- Main domain ----
        $autoinstallDomain = '';
        if($installType === 'autoinstall')
        {
            $mainDomain = isset($input['main_domain']) ? strtolower(trim($input['main_domain'])) : '';
            if(empty($mainDomain) || $mainDomain === '__manual__')
            {
                throw new \Exception(Lang::absoluteT('sslConfigDomainRequired'));
            }
            $autoinstallDomain = strpos($mainDomain, '*.') === 0 ? substr($mainDomain, 2) : $mainDomain;
            if(Cpanel::getServiceByDomain($userId, $autoinstallDomain) === false)
            {
                throw new \Exception(sprintf(Lang::absoluteT('sslConfigDomainNotInCpanel'), $autoinstallDomain));
            }
        }
        elseif(!empty($ownCsr))
        {
            $csrSubject = @openssl_csr_get_subject($ownCsr);
            if(!$csrSubject || empty($csrSubject['CN']))
            {
                throw new \Exception(Lang::absoluteT('csr_error'));
            }
            CertificateService::assertCsrKey($ownCsr);
            $mainDomain = strtolower(trim($csrSubject['CN']));
        }
        else
        {
            $mainDomain = isset($input['manual_domain']) ? strtolower(Domain::toAscii($input['manual_domain'])) : '';
        }
        if(empty($mainDomain))
        {
            $mainDomain = strtolower(trim((string)$params['domain']));
        }
        if(empty($mainDomain))
        {
            throw new \Exception(Lang::absoluteT('sslConfigDomainRequired'));
        }

        $domainValidation = new Domain();
        $isWildcardCn     = strpos($mainDomain, '*.') === 0;
        $domainValidation->validateDomain($mainDomain, $isWildcardCn);

        // ---- SANs ----
        $sanDomains   = $this->parseSslDomainList(isset($input['san_domains']) ? $input['san_domains'] : '');
        $wildcardSans = [];
        foreach($this->parseSslDomainList(isset($input['wildcard_san']) ? $input['wildcard_san'] : '') as $wildcardSan)
        {
            $wildcardSans[] = strpos($wildcardSan, '*.') === 0 ? $wildcardSan : '*.' . $wildcardSan;
        }
        // SANs written into the client's own CSR count when the SAN fields are left empty
        if(!$sanDomains && !$wildcardSans && !empty($ownCsr) && ($settings['sansLimit'] > 0 || $settings['wildcardSansLimit'] > 0))
        {
            foreach(CertificateService::csrSans($ownCsr) as $csrSan)
            {
                if(strpos($csrSan, '*.') === 0)
                {
                    $wildcardSans[] = $csrSan;
                }
                else
                {
                    $sanDomains[] = $csrSan;
                }
            }
        }
        // a SAN equal to the common name, or listed twice, would use a paid slot for nothing
        $sanDomains   = array_values(array_unique(array_diff($sanDomains, [$mainDomain])));
        $wildcardSans = array_values(array_unique(array_diff($wildcardSans, [$mainDomain])));

        if(count($sanDomains) > $settings['sansLimit'])
        {
            throw new \Exception(sprintf(Lang::absoluteT('using_max_san'), $settings['sansLimit']));
        }
        if(count($wildcardSans) > $settings['wildcardSansLimit'])
        {
            throw new \Exception(sprintf(Lang::absoluteT('using_max_san_wildcard'), $settings['wildcardSansLimit']));
        }
        $ipAllowed = $this->sanIpAllowed($params['packageid']);
        foreach($sanDomains as $sanDomain)
        {
            $domainValidation->validateDomain($sanDomain, false, $ipAllowed);
        }
        foreach($wildcardSans as $wildcardSan)
        {
            $domainValidation->validateDomain($wildcardSan, true);
        }

        // ---- CSR ----
        $privateKey = '';
        if(!empty($ownCsr))
        {
            $csrCode = $ownCsr;
        }
        else
        {
            $commonName = $mainDomain;
            if(!$isWildcardCn && $installType === 'autoinstall' && $includeWww && strpos($commonName, 'www.') !== 0)
            {
                $commonName = 'www.' . $commonName;
            }

            $privKey = openssl_pkey_new([
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]);
            if(!$privKey || !openssl_pkey_export($privKey, $privateKey))
            {
                throw new \Exception(Lang::absoluteT('error_csr_generate'));
            }

            $dn  = array_filter([
                'countryName'            => strtoupper(!empty($input['csr_country']) ? $input['csr_country'] : 'US'),
                'stateOrProvinceName'    => isset($input['csr_state']) ? trim($input['csr_state']) : '',
                'localityName'           => isset($input['csr_city']) ? trim($input['csr_city']) : '',
                'organizationName'       => !empty($input['csr_org']) ? trim($input['csr_org']) : $commonName,
                'organizationalUnitName' => isset($input['csr_org_unit']) ? trim($input['csr_org_unit']) : '',
                'commonName'             => $commonName,
                'emailAddress'           => isset($input['csr_email']) ? trim($input['csr_email']) : '',
            ], function($v) { return $v !== ''; });
            $csr = openssl_csr_new($dn, $privKey, ['digest_alg' => 'sha256']);
            if(!$csr || !openssl_csr_export($csr, $csrCode))
            {
                throw new \Exception(Lang::absoluteT('error_csr_generate'));
            }
        }

        $csrSubject = openssl_csr_get_subject($csrCode);
        if(!$csrSubject || empty($csrSubject['CN']))
        {
            throw new \Exception(Lang::absoluteT('csr_error'));
        }
        $commonName = $csrSubject['CN'];

        // ---- Validation methods (format expected by SSLStepThree) ----
        $allDomains = array_values(array_unique(array_merge([$commonName], array_diff(array_merge($sanDomains, $wildcardSans), [$commonName]))));
        $method     = [];
        $approver   = [];

        if($dcvMethod === 'email')
        {
            $approverEmail = isset($input['approver_email']) ? trim($input['approver_email']) : '';
            if(empty($approverEmail))
            {
                throw new \Exception(Lang::absoluteT('sslConfigApproverEmailRequired'));
            }

            $otherDomains = array_values(array_diff($allDomains, [$commonName]));
            $sanApprovers = !empty($otherDomains)
                ? AutoInstallHelpers::pickApproverEmails($this->getSslApiForProduct($params['packageid']), $otherDomains)
                : [];
        }

        \ModulesGarden\TSSGGSModule\App\Libs\SanUpgrade::assertWithinLimits($serviceId, $commonName, $allDomains, \ModulesGarden\TSSGGSModule\App\Libs\SanUpgrade::paidQuantities($params));

        foreach($allDomains as $domain)
        {
            $domainMethod = $dcvMethod;
            // File based validation is not possible for wildcard names
            if(strpos($domain, '*.') === 0 && in_array($domainMethod, ['http', 'https']))
            {
                $domainMethod = 'dns';
            }
            $method[$domain] = $domainMethod;

            if($domainMethod !== 'email')
            {
                continue;
            }
            if($domain === $commonName)
            {
                $approver[$domain] = $approverEmail;
                continue;
            }

            if(empty($sanApprovers[$domain]))
            {
                throw new \Exception(sprintf(Lang::absoluteT('sslConfigNoApproverForDomain'), $domain));
            }
            $approver[$domain] = $sanApprovers[$domain];
        }

        // ---- Contacts ----
        $client = Client::where('id', $userId)->first();
        if($settings['adminFormHidden'] && $client)
        {
            $contact = [
                'firstname'   => $client->firstname,
                'lastname'    => $client->lastname,
                'jobtitle'    => 'IT',
                'email'       => $client->email,
                'phonenumber' => $client->phonenumber,
                'address1'    => $client->address1,
                'city'        => $client->city,
                'state'       => $client->state,
                'postcode'    => $client->postcode,
                'country'     => $client->country,
            ];
        }
        else
        {
            $contact = [
                'firstname'   => isset($input['admin_firstname']) ? trim($input['admin_firstname']) : '',
                'lastname'    => isset($input['admin_lastname']) ? trim($input['admin_lastname']) : '',
                'jobtitle'    => isset($input['admin_title']) ? trim($input['admin_title']) : '',
                'email'       => isset($input['admin_email']) ? trim($input['admin_email']) : '',
                'phonenumber' => isset($input['admin_phone']) ? trim($input['admin_phone']) : '',
                'address1'    => isset($input['admin_addressline1']) ? trim($input['admin_addressline1']) : '',
                'city'        => isset($input['admin_city']) ? trim($input['admin_city']) : '',
                'state'       => isset($input['admin_region']) ? trim($input['admin_region']) : '',
                'postcode'    => isset($input['admin_postalcode']) ? trim($input['admin_postalcode']) : '',
                'country'     => isset($input['admin_country']) ? strtoupper(trim($input['admin_country'])) : '',
            ];
            foreach(['firstname', 'lastname', 'email', 'phonenumber'] as $requiredField)
            {
                if(empty($contact[$requiredField]))
                {
                    throw new \Exception(Lang::absoluteT('sslConfigContactRequired'));
                }
            }
        }

        // ---- Place the order (same path as WHMCS configuressl.php step three) ----
        $stepParams = array_merge($params, $contact, [
            'csr'      => $csrCode,
            'method'   => $method,
            'approver' => $approver,
        ]);
        $result = (new SSLStepThree())->run($stepParams);

        $ssl = SSL::getByServiceId($serviceId);
        if(empty($ssl->remoteid))
        {
            throw new \Exception(is_array($result) && !empty($result['error']) ? $result['error'] : Lang::absoluteT('sslConfigOrderFailed'));
        }

        Capsule::table('tblsslorders')->where('serviceid', $serviceId)->update([
            'status'         => 'Configuration Submitted',
            'completiondate' => date('Y-m-d H:i:s'),
        ]);

        if($installType === 'autoinstall')
        {
            $isWildcardOrder  = $isWildcardCn || !empty($wildcardSans);
            $dnsFallbackEmail = ($dcvMethod === 'dns' && !empty($input['dns_fallback_email'])) ? trim($input['dns_fallback_email']) : '';

            $validateTaskData = [
                'domain'       => $autoinstallDomain,
                'api_order_id' => (int)$ssl->remoteid,
                'is_wildcard'  => $isWildcardOrder,
            ];
            if($dcvMethod === 'dns')
            {
                $validateTaskData['dns_check_start'] = time();
            }
            if($dnsFallbackEmail)
            {
                $validateTaskData['dns_fallback_email'] = $dnsFallbackEmail;
            }

            AutoInstallHelpers::onConfigured($serviceId, $userId, [
                'install_type'       => 'autoinstall',
                'domain'             => $autoinstallDomain,
                'common_name'        => $commonName,
                'dcv_method'         => $dcvMethod,
                'dns_fallback_email' => $dnsFallbackEmail,
                'is_wildcard'        => $isWildcardOrder,
                'private_key'        => \encrypt($privateKey),
                'created_at'         => date('Y-m-d H:i:s'),
            ], $validateTaskData);
        }

        // keep the key the module generated (here or in the CSR generator) for "Install on server"
        try
        {
            if(!empty($privateKey))
            {
                CertificateService::storePrivateKey($serviceId, $privateKey);
            }
            elseif(!empty($ownCsr))
            {
                CertificateService::rememberGeneratedKey($serviceId, $ownCsr);
            }
        }
        catch(\Throwable $e)
        {
        }

        $response = [
            'success'        => true,
            'install_type'   => $installType,
            'approver_email' => isset($approver[$commonName]) ? $approver[$commonName] : '',
        ];

        // Manual enrollment with a generated CSR - the client has to take the private key now
        if($installType === 'manual' && !empty($privateKey))
        {
            $response['private_key']     = $privateKey;
            $response['private_key_handle'] = \ModulesGarden\TSSGGSModule\App\Libs\PrivateKeyDownload::store((int)$serviceId, $privateKey);
        }

        return $response;
    }

    /**
     * State-changing client actions must be POSTed with the WHMCS CSRF token - WHMCS itself verifies the token
     * only on its own forms, so module actions reachable via GET would otherwise be open to CSRF.
     */
    private function requireCsrfToken()
    {
        if($_SERVER['REQUEST_METHOD'] !== 'POST' || !\ModulesGarden\TSSGGSModule\App\Libs\CsrfToken::valid($_POST['token'] ?? ''))
        {
            throw new \Exception(Lang::absoluteT('invalidCsrfToken'));
        }
    }

    /**
     * Per-service MySQL lock so a double submit cannot place two orders / create two subscriptions.
     * Returns false when another request holds the lock.
     */
    private function acquireServiceLock($serviceId)
    {
        $row = Capsule::select('SELECT GET_LOCK(?, 0) AS l', ['tssggs_service_' . (int)$serviceId]);

        return !empty($row) && (int)$row[0]->l === 1;
    }

    private function releaseServiceLock($serviceId)
    {
        Capsule::select('SELECT RELEASE_LOCK(?)', ['tssggs_service_' . (int)$serviceId]);
    }

    private function extractDomainsFromInput($domains)
    {
        $items = preg_split('/[\s,;]+/', trim((string)$domains));
        $items = array_filter(array_map('trim', $items));
        $items = array_values(array_unique(array_map('strtolower', $items)));

        return $items;
    }
}
