<?php

namespace ModulesGarden\Servers\TTSGGSModule\app\controllers\server\clientarea;

use ModulesGarden\Servers\TTSGGSModule\app\models\whmcs\Client;
use ModulesGarden\Servers\TTSGGSModule\app\models\whmcs\Order;
use ModulesGarden\Servers\TTSGGSModule\app\models\whmcs\Product;
use ModulesGarden\Servers\TTSGGSModule\app\models\whmcs\Service;
use ModulesGarden\Servers\TTSGGSModule\app\models\whmcs\SSL;
use ModulesGarden\Servers\TTSGGSModule\app\repository\whmcs\Config;
use ModulesGarden\Servers\TTSGGSModule\app\repository\whmcs\Domain;
use ModulesGarden\Servers\TTSGGSModule\app\repository\whmcs\ProductConfigOptions;
use ModulesGarden\Servers\TTSGGSModule\app\services\InvoiceRenew;
use ModulesGarden\Servers\TTSGGSModule\core\Lang;
use ModulesGarden\Servers\TTSGGSModule\core\process\AbstractController;
use ModulesGarden\TTSGGSModule\App\Helpers\EmailTemplates;
use ModulesGarden\TTSGGSModule\App\Libs\SSLTrustCenterApi;
use ModulesGarden\TTSGGSModule\App\Models\Request;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\ProductRepository;
use WHMCS\Database\Capsule;

class home extends AbstractController {

    function indexHTML($input, $vars = []) {

        try{

            $serviceId = $input['id'];
            $ssl = SSL::getByServiceId($serviceId);

            if(isset($ssl->status) && $ssl->status == 'Awaiting Configuration') {
                return $this->awaitingConfiguration($serviceId, $ssl);
            }

            if(isset($ssl->status) && $ssl->status == 'Configuration Submitted') {
                return $this->configurationSubmitted($input, $ssl);
            }

        } catch (\Exception $e) {

            return ['tpl' => 'error','vars' => ['error' => $e->getMessage()]];

        }

        return ['tpl' => 'home','vars' => $vars];

    }

    public function awaitingConfiguration($serviceId, $ssl)
    {
        $service = Service::where('id', $serviceId)->first();
        $order = Order::where('id', $service->orderid)->first();
        $product = Product::where('id', $service->packageid)->first();

        $vars['product_title'] = sprintf(Lang::absoluteT('product_title_awaiting_configuration'), $product->name);
        $vars['domain'] = $order->domain;
        $vars['product_name'] = $product->name;
        $vars['order_number'] = isset($order->ordernum) && !empty($order->ordernum) ? $order->ordernum : $order->id;
        $vars['url'] = (new Config())->getConfigureSSLUrl($ssl->id, $serviceId);
        return ['tpl' => 'awaiting_configuration','vars' => $vars];
    }

    public function configurationSubmitted($input , $ssl)
    {
        global $CONFIG;

        $vars = [];

        $service = Service::where('id', $input['id'])->first();
        $product = Product::where('id', $service->packageid)->first();
        $client = Client::where('id', $service->userid)->first();

        $vars['configurableOptions'] = ProductConfigOptions::get($product->id, $input['id'], $service->billingcycle);

        $orderDetails = Request::getOrder($input['id']);
        $order = $orderDetails['orderData'];

        if($order['order']['status'] == 'active')
        {
            $certificate = $orderDetails['orderFiles'];
            return $this->certificateActive([
                'order' => $order,
                'ssl' => $ssl,
                'input' => $input,
                'service' => $service,
                'product' => $product,
                'client' => $client,
                'certificate' => $certificate
            ]);
        }

        $vars['product_title'] = sprintf(Lang::absoluteT('product_title_configuration'), $product->name);
        $vars['product_name'] = $product->name;
        $vars['api_order_id'] = $order['order']['id'];
        $vars['vendor_order_id'] = is_array($order['items']) ? reset($order['items'])['vendor']['id'] : "";
        $vars['domain'] = $service->domain;
        $vars['status'] = $order['order']['status'];
        $vars['valid_from'] = '';
        $vars['valid_till'] = '';
        $vars['subscribtion_starts'] = '';
        $vars['subscribtion_ends'] = '';
        $vars['next_reissue'] = '';
        $vars['ssl_issuer'] = '';
        $vars['order_details']['registration_date'] = date('l F jS, Y',strtotime($service->regdate));
        $vars['order_details']['expiration_date'] = $service->nextduedate != '0000-00-00' ? date('l F jS, Y',strtotime($service->nextduedate)) : "";
        $vars['order_details']['reccuring_amount'] = formatCurrency($service->amount, $client->currency);
        $vars['order_details']['billing_cycle'] = ucfirst($service->billingcycle);
        $vars['order_details']['payment_method'] = ucfirst($service->paymentmethod);
        $vars['order_details']['csr_status_updated'] = date('Y-m-d H:i');
        $vars['order_details_api'] = $order;
        $vars['serviceid'] = $service->id;
        $vars['countSAN'] = 0;
        $vars['countSANWildcard'] = 0;
        $domains = is_array($order['items']) ? reset($order['items'])['domains'] : [];

        $mainDomain = $order['orderData']['common_name']['name'];

        $vars['additional_names'] = [];

        foreach ($domains as $domain)
        {
            if($domain['name'] == $mainDomain)
            {
                $vars['mainDomainMethod'] = $domain['method'];
                $vars['mainDomainValidation'] = $domain['validation'];
            }
            else
            {
                $vars['additional_names'][] = $domain;
            }
        }

        $vars['whmcs_url'] = $CONFIG['SystemURL'];

        foreach ($order['orderData']['alternative_names'] as $alternativeName)
        {
            if (strpos($alternativeName['domain'], '*.') !== false)
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

        return ['tpl' => 'configuration_submitted', 'vars' => $vars];
    }

    public function sendCertificateHTML($input, $vars = [])
    {
        $serviceId = $input['id'];
        $certificate = Request::getOrder($serviceId);


        $filename = str_replace('.', '_', $certificate['orderData']['orderData']['common_name']['name']).'.crt';
        $files = $certificate['orderFiles']['files'];

        $fileContentCrt = '';
        $fileContentCA = '';
        foreach ($files as $file)
        {
            if($file['name'] == $filename)
            {
                $fileContentCrt = $file['content'];
            }
            if (strpos($file['name'], 'CA.crt') !== false)
            {
                $fileContentCA = $file['content'];
            }
        }


        $pathAttachemts = false;
        $checkSettings = Capsule::schema()->hasTable('tblfileassetsettings');
        if($checkSettings !== false) {
            $filesetting = Capsule::table('tblfileassetsettings')->where('asset_type', 'email_attachments')->first();
            if(isset($filesetting->storageconfiguration_id) && !empty($filesetting->storageconfiguration_id))
            {
                $checkStorage = Capsule::schema()->hasTable('tblstorageconfigurations');
                if($checkStorage !== false) {

                    $storage = Capsule::table('tblstorageconfigurations')->where('id', $filesetting->storageconfiguration_id)->first();
                    if(isset($storage->settings) && !empty($storage->settings))
                    {
                        $storageData = json_decode($storage->settings, true);
                        if(isset($storageData['local_path']) && !empty($storageData['local_path']))
                        {
                            $pathAttachemts = $storageData['local_path'];
                        }
                    }
                }
            }
        }


        $attachments = [];
        if(!empty($fileContentCrt)) {
            if($pathAttachemts === false) {
                $tmp_ca_code = tempnam("/tmp", "FOO");
                $handle = fopen($tmp_ca_code, "w");
                fwrite($handle, $fileContentCrt);
                fclose($handle);

                $attachments[] = array(
                    'displayname' => 'crt_certificate.crt',
                    'path' => $tmp_ca_code
                );
            }
            else
            {
                $filetemp = $pathAttachemts.DIRECTORY_SEPARATOR.$serviceId.$input['params']['accountid'].'_crt_certificate.crt';
                file_exists($filetemp) or touch($filetemp);
                file_put_contents($filetemp, $fileContentCrt);

                $attachments[] = array(
                    'displayname' => $serviceId.$input['params']['accountid'].'_crt_certificate.crt',
                    'filename' => $serviceId.$input['params']['accountid'].'_crt_certificate.crt'
                );
            }
        }

        if(!empty($fileContentCA)) {
            if($pathAttachemts === false) {
                $tmp_crt_code = tempnam("/tmp", "FOO");
                $handle = fopen($tmp_crt_code, "w");
                fwrite($handle, $fileContentCA);
                fclose($handle);

                $attachments[] = array(
                    'displayname' => 'ca_certificate.crt',
                    'path' => $tmp_crt_code
                );
            }
            else
            {
                $filetemp = $pathAttachemts.DIRECTORY_SEPARATOR.$serviceId.$input['params']['accountid'].'_ca_certificate.crt';
                file_exists($filetemp) or touch($filetemp);
                file_put_contents($filetemp, $fileContentCA);

                $attachments[] = array(
                    'displayname' => $serviceId.$input['params']['accountid'].'_ca_certificate.crt',
                    'filename' => $serviceId.$input['params']['accountid'].'_ca_certificate.crt'
                );
            }
        }



        EmailTemplates::sendEmail(EmailTemplates::SEND_CERTIFICATE_TEMPLATE, $serviceId, [
            'domain' => $filename,
            'ssl_certyficate' => nl2br($fileContentCA),
            'crt_code' => nl2br($fileContentCrt),
        ], $attachments);



        if(!empty($fileContentCA)) {

            unlink($tmp_ca_code);

        }

        if(!empty($orderStatus['crt_code'])) {

            unlink($tmp_crt_code);

        }

        redir('action=productdetails&id='.$input['id'].'&sendEmail=true', 'clientarea.php');
    }

    public function downloadCRTHTML($input, $vars = [])
    {
        $serviceId = $input['id'];
        $certificate = Request::getOrder($serviceId);

        $filename = str_replace('.', '_', $certificate['orderData']['orderData']['common_name']['name']).'.crt';
        $files = $certificate['orderFiles']['files'];

        $fileContent = '';

        foreach ($files as $file)
        {
            if($file['name'] == $filename)
            {
                $fileContent = $file['content'];
            }
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.$filename);
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        print $fileContent;
        exit;

    }

    public function downloadICRTHTML($input, $vars = [])
    {
        $serviceId = $input['id'];
        $certificate = Request::getOrder($serviceId);

        $files = $certificate['orderFiles']['files'];

        $filename = 'CA.crt';
        $fileContent = '';

        foreach ($files as $file)
        {

            if (strpos($file['name'], 'CA.crt') !== false)
            {
                $fileContent = $file['content'];
                $filename = $file['name'];
            }
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.$filename);
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        print $fileContent;
        exit;

    }

    public function downloadSSLHTML($input, $vars = [])
    {
        $serviceId = $input['id'];
        $certificate = Request::getOrder($serviceId);

        $mainCrt = str_replace('.', '_', $certificate['orderData']['orderData']['common_name']['name']).'.crt';
        $files = $certificate['orderFiles']['files'];

        $filename = 'certificates.crt';
        $fileContent = '';

        foreach ($files as $file)
        {

            if (strpos($file['name'], 'CA.crt') !== false)
            {
                $fileContent .= $file['content'];
            }
            if($file['name'] == $mainCrt)
            {
                $fileContent .= $file['content'];
            }
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.$filename);
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        print $fileContent;
        exit;

    }

    public function reissueSSLHTML($input, $vars = [])
    {
        try{

            $serviceId = $input['id'];
            $ssl = SSL::getByServiceId($serviceId);
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


        } catch (\Exception $e) {

            return ['tpl' => 'error','vars' => ['error' => $e->getMessage()]];

        }

        return ['tpl' => 'home','vars' => $vars];

    }

    public function reissueStep1($input, $serviceId, $ssl, $certificate)
    {
        $countries = file_get_contents(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__DIR__))))))).DS.'resources'.DS.'country'.DS.'dist.countries.json');

        $configuration = (new AddonModuleRepository())->getModuleConfiguration();

        $clientData = $input['params']['model']->client->toArray();
        if(!isset($configuration['sslSettings']['useProfileDetailsForCsr']) || empty($configuration['sslSettings']['useProfileDetailsForCsr']))
        {
            foreach ($clientData as $key => $value)
            {
                $clientData[$key] = '';
            }
        }

        if(isset($configuration['sslSettings']['useProfileDetailsForCsr']) && !empty($configuration['sslSettings']['useProfileDetailsForCsr']))
        {
            $configuration['sslSettings']['defaultCsrCountry'] = false;
        }

        $vars = [];
        $vars['countries'] = json_decode($countries, true);

        $vars['enable_csr'] = isset($configuration['sslSettings']['enableCsrGenerator']) ? $configuration['sslSettings']['enableCsrGenerator'] : false;
        $vars['use_profile_data'] = isset($configuration['sslSettings']['useProfileDetailsForCsr']) ? $configuration['sslSettings']['useProfileDetailsForCsr'] : false;
        $vars['default_csr_country'] = isset($configuration['sslSettings']['defaultCsrCountry']) ? $configuration['sslSettings']['defaultCsrCountry'] : false;
        $vars['domain'] = $input['params']['domain'];
        $vars['client'] = $clientData;

        $vars['sans'] = isset($params['configoptions']['sans']) ? $params['configoptions']['sans'] : 0;
        $vars['sans_wildcard'] = isset($params['configoptions']['sans_wildcard']) ? $params['configoptions']['sans_wildcard'] : 0;

        $vars['sans'] += $params['configoption7'];
        $vars['sans_wildcard'] += $params['configoption8'];
        $vars['serviceid'] = $input['id'];
        $vars['lang_san'] = sprintf(Lang::absoluteT('sans_additional'), $vars['sans']);
        $vars['lang_san_wildcard'] = sprintf(Lang::absoluteT('sans_additional_wildcard'), $vars['sans_wildcard']);

        $vars['errors'] = isset($_SESSION['mg-error']) ? $_SESSION['mg-error'] : false;
        $vars['post_csr'] = isset($_SESSION['post_csr']) ? $_SESSION['post_csr'] : false;
        $vars['post_san'] = isset($_SESSION['post_san']) ? $_SESSION['post_san'] : false;
        $vars['post_san_wildcard'] = isset($_SESSION['post_san_wildcard']) ? $_SESSION['post_san_wildcard'] : false;

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

        try {

            $fields = [
                'sans_domains' => isset($input['sans_domains']) ? $input['sans_domains'] : '',
                'wildcard_san' => isset($input['sans_domains_wildcard']) ? $input['sans_domains_wildcard'] : '',
            ];

            $decode = $this->decodeCSR($input['csr']);
            $domains = $this->validateDomains($decode['CN'], $fields, $input['params']);

            $configProduct = (new ProductRepository())->getProductConfiguration($input['params']['packageid']);
            $addonConfig = (new AddonModuleRepository())->getModuleConfiguration();

            $available_method_validation = explode(',',$configProduct['dcv']);
            if(isset($addonConfig['sslSettings']['disableEmailValidation']) && !empty($addonConfig['sslSettings']['disableEmailValidation']))
            {
                $key = array_search('email', $available_method_validation);
                if($key !== false) unset($available_method_validation[$key]);

                $temp_available_method_validation = [];
                foreach ($available_method_validation as $method)
                {
                    $temp_available_method_validation[] = $method;
                }
                $available_method_validation = $temp_available_method_validation;
            }
            $vars['available_method_validation'] = json_encode($available_method_validation);

            $provider = strtolower($configProduct['provider']);
            $credentials = $addonConfig['credentials'][$provider];

            if($provider == 'tss' && $credentials['OperationMode'] == 'sandbox')
            {
                $credentials['PartnerCode'] = $credentials['TestPartnerCode'];
                $credentials['AuthToken'] =  $credentials['TestAuthToken'];
            }

            $api = new SSLTrustCenterApi($configProduct['provider'], $credentials['PartnerCode'], $credentials['AuthToken'], $configProduct['category']);
            $approvers = $api->getDomainEmails($domains);
            $vars['approvers'] = $this->approversToOptions($approvers);

            if(isset($input['customAction']) && $input['customAction'] == 'sendReissue')
            {
                $vars['error'] = $this->reissueStep3($input);
            }


        } catch (\Exception $e) {

            $_SESSION['post_csr'] = $input['csr'];
            $_SESSION['post_san'] = $input['sans_domains'];
            $_SESSION['post_san_wildcard'] = $input['sans_domains_wildcard'];
            $_SESSION['mg-error'] = $e->getMessage();
            redir('action=productdetails&id='.$input['id'].'&mg-action=reissueSSL', 'clientarea.php');

        }

        $vars['postData'] = $_POST;

        return ['tpl' => 'reissue_step2', 'vars' => $vars];
    }

    public function reissueStep3($input)
    {

        try{

            $serviceId = $input['id'];
            $certificate = Request::getOrder($serviceId);

            $configProduct = (new ProductRepository())->getProductConfiguration($input['params']['packageid']);
            $addonConfig = (new AddonModuleRepository())->getModuleConfiguration();

            $provider = strtolower($configProduct['provider']);
            $credentials = $addonConfig['credentials'][$provider];

            if($provider == 'tss' && $credentials['OperationMode'] == 'sandbox')
            {
                $credentials['PartnerCode'] = $credentials['TestPartnerCode'];
                $credentials['AuthToken'] =  $credentials['TestAuthToken'];
            }

            $api = new SSLTrustCenterApi($configProduct['provider'], $credentials['PartnerCode'], $credentials['AuthToken'], $configProduct['category']);

            $decode = openssl_csr_get_subject($input['csr']);
            $mainMethod = $input['method'][$decode['CN']] == 'email' ? $input['approver'][$decode['CN']] : $input['method'][$decode['CN']];

            $request = Request::where('serviceid', $serviceId)->where('name', 'addOrder')->first();
            $requestOrder = json_decode(\decrypt($request->request), true);

            $san = [];
            foreach ($input['method'] as $domain_san => $method_san)
            {
                if($domain_san == $decode['CN']) continue;
                $methodSan = $method_san == 'email' ? $input['approver'][$domain_san] : $method_san;
                $san[] = ['name' => $domain_san, 'validation_method' => $methodSan];
            }

            $reissueData = [
                'csr' => trim($input['csr']),
                'common_name' => [
                    'name' => $decode['CN'],
                    'validation_method' => $mainMethod
                ],
                'existing_alternative_names' => isset($requestOrder['alternative_names']) ? $requestOrder['alternative_names'] : [],
                'submitted_alternative_names' => $san
            ];
            $reissue = $api->reissueCertificate($certificate['orderData']['order']['id'], $reissueData);

            $requestOrder['csr'] = $reissueData['csr'];
            $requestOrder['common_name'] = $reissueData['common_name'];
            $requestOrder['alternative_names'] = $reissueData['submitted_alternative_names'];

            Request::where('serviceid', $serviceId)->where('name', 'addOrder')->update([
                'request' => \encrypt(json_encode($requestOrder)),
                'status' => 'pending',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $orderData = $api->getOrder($reissue['order']['id'], $serviceId);
            $orderFiles = [];
            try {
                $orderFiles = $api->getCertificateFiles($reissue['order']['id']);
            } catch (\Exception $exception) {}
            $request = ['orderData' => $orderData, 'orderFiles' => $orderFiles];
            Request::where('serviceid', $serviceId)->where('name', 'certificate')->update([
                'request' => \encrypt(json_encode($request)),
                'status' => 'pending',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            Service::where('id', $serviceId)->update(['domain' => $decode['CN']]);

            $_SESSION['mg-success'] = Lang::absoluteT('reissue_success');
            redir('action=productdetails&id='.$serviceId, 'clientarea.php');

        } catch (\Exception $e) {

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
        return $decode;
    }

    public function validateDomains($commonName, $fields, $params = [])
    {
        $countSan = isset($params['configoptions']['sans']) ? $params['configoptions']['sans'] : 0;
        $countSanWildCard = isset($params['configoptions']['sans_wildcard']) ? $params['configoptions']['sans_wildcard'] : 0;

        $countSan += $params['configoption7'];
        $countSanWildCard += $params['configoption8'];

        $domainValidation = new Domain();

        $domains = '';
        if (!empty($fields['sans_domains'])) {

            $san = explode(',', $fields['sans_domains']);

            if($countSan < count($san))
            {
                throw new \Exception(sprintf(Lang::absoluteT('using_max_san'),$countSan));
            }

            foreach ($san as $domain)
            {
                if(empty($domain)) continue;
                $domainValidation->validateDomain($domain);
            }

            $domains .= $fields['sans_domains'];
        }
        if (!empty($fields['wildcard_san'])) {

            $wildcard = explode(',', $fields['wildcard_san']);

            if($countSanWildCard < count($wildcard))
            {
                throw new \Exception(sprintf(Lang::absoluteT('using_max_san_wildcard'),$countSanWildCard));
            }

            foreach ($wildcard as $domain)
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

        foreach ($domains as $key => $domain)
        {
            if(empty($domain)) unset($domains[$key]);
            $domains[$key] = trim($domain);
        }

        return $domains;
    }

    public function approversToOptions($approvers)
    {
        $results = [];
        foreach ($approvers as $approver) {
            $options = '';
            foreach ($approver['emails'] as $option) {
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

        $vars['product_title'] = sprintf(Lang::absoluteT('product_title_active'), $params['product']->name);
        $vars['product_name'] = $params['product']->name;
        $vars['api_order_id'] = $params['order']['order']['id'];
        $vars['vendor_order_id'] = reset($params['order']['items'])['vendor']['id'];
        $vars['domain'] = $params['service']->domain;
        $vars['status'] = $params['order']['order']['status'];
        $vars['subscribtion_starts'] = date('F j, Y', strtotime($itemOrder['subscription']['begin']));
        $vars['subscribtion_ends'] = date('F j, Y', strtotime($itemOrder['subscription']['end']));
        $vars['serviceid'] = $params['service']->id;
        $vars['order_details']['registration_date'] = date('l F jS, Y',strtotime($params['service']->regdate));
        $vars['order_details']['expiration_date'] = $params['service']->nextduedate != '0000-00-00' ? date('l F jS, Y',strtotime($params['service']->nextduedate)) : "";
        $vars['order_details']['reccuring_amount'] = formatCurrency($params['service']->amount, $params['client']->currency);
        $vars['order_details']['billing_cycle'] = ucfirst($params['service']->billingcycle);
        $vars['order_details']['payment_method'] = ucfirst($params['service']->paymentmethod);
        $vars['whmcs_url'] = $CONFIG['SystemURL'];
        $vars['valid_from'] = date('F j, Y', strtotime($params['certificate']['validity']['begin']));
        $vars['valid_till'] = date('F j, Y', strtotime($params['certificate']['validity']['end']));

        $vars['visibleRenewButtonBeforeExpiration'] = $configuration['sslSettings']['visibleRenewButtonBeforeExpiration'];
        $vars['visibleRenewButtonAfterExpiration'] = $configuration['sslSettings']['visibleRenewButtonAfterExpiration'];

        $today = strtotime(date('Y-m-d'));
        $expiryDate = strtotime($params['certificate']['validity']['end']);
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

        $now = strtotime(date('Y-m-d'));
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

        return ['tpl' => 'certificate_active', 'vars' => $vars];
    }

    public function renewHTML($input, $vars = [])
    {
        global $CONFIG;

        try
        {

            $invoice = new InvoiceRenew();
            $invoiceId = $invoice->checkInvoiceAlreadyCreated($input['id']);

            if($invoiceId)
            {
                $existInvoiceID = $invoiceId;
                $_SESSION['mg-success'] = Lang::absoluteT('renew_exists').' <a target="_blank" href="'.$CONFIG['SystemURL'].'/viewinvoice.php?id='.$existInvoiceID.'">'.Lang::absoluteT('Click here to view the invoice').'</a>';
                redir('action=productdetails&id='.$input['id'], 'clientarea.php');

            }

            $invoiceId = $invoice->createInvoice($input['id']);
            $_SESSION['mg-success'] = Lang::absoluteT('renew_invoice_generated').' <a target="_blank" href="'.$CONFIG['SystemURL'].'/viewinvoice.php?id='.$invoiceId.'">'.Lang::absoluteT('Click here to view the invoice').'</a>';
            redir('action=productdetails&id='.$input['id'], 'clientarea.php');

        } catch(\Exception $e) {

            $_SESSION['mg-error'] = $e->getMessage();
            redir('action=productdetails&id='.$input['id'], 'clientarea.php');

        }


    }

    public function checkCSRJSON($input, $vars = [])
    {
        $decode = openssl_csr_get_subject($input['csr']);
        return ['json' => ['status' => 'success', 'data' => $decode]];
    }

    public function generateCSRJSON($input, $vars = [])
    {
        try {

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

            if (!$csr)
            {
                return ['json' => ['status' => 'error', 'message' => Lang::absoluteT('error_csr_generate')]];
            }

            openssl_csr_export($csr, $csrOut);

            return ['json' => ['status' => 'success', 'data' => ['csr' => $csrOut, 'private_key' => \encrypt($pKeyOut)]]];

        } catch (\Exception $e) {

            return ['json' => ['status' => 'error', 'message' => $e->getMessage()]];

        }

    }

    public function downloadKeyHTML($input, $vars = [])
    {
        $pkey = $input['pkey'];
        $decodeKey = \decrypt(str_replace(' ', '+', $pkey));
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=privatekey.pem');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        print $decodeKey;
        exit;

    }

    public function cancelHTML($input, $vars = [])
    {
        $service = Service::where('id', $input['id'])->first();
        $configProduct = (new ProductRepository())->getProductConfiguration($service->packageid);
        $addonConfig = (new AddonModuleRepository())->getModuleConfiguration();

        $provider = strtolower($configProduct['provider']);
        $credentials = $addonConfig['credentials'][$provider];

        if($provider == 'tss' && $credentials['OperationMode'] == 'sandbox')
        {
            $credentials['PartnerCode'] = $credentials['TestPartnerCode'];
            $credentials['AuthToken'] =  $credentials['TestAuthToken'];
        }

        $api = new SSLTrustCenterApi($configProduct['provider'], $credentials['PartnerCode'], $credentials['AuthToken'], $configProduct['category']);
        $api->cancelOrder($input['remoteid'], 'Cancelled by customer');

        Service::where('id', $input['id'])->update([
            'domainstatus' => 'Cancelled'
        ]);

        Request::where('name', 'addOrder')->where('serviceid', $input['id'])->update([
            'status' => 'cancelled'
        ]);

        redir('action=productdetails&id='.$input['id'], 'clientarea.php');
    }

    public function downloadCSRHTML($input, $vars = [])
    {
        $serviceId = $input['id'];
        $certificate = Request::getOrder($serviceId);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.$certificate['orderData']['orderData']['common_name']['name'].'.csr');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        print $certificate['orderData']['orderData']['csr'];
        exit;
    }

    public function downloadAuthFileHTML($input, $vars = [])
    {
        $serviceId = $input['id'];
        $certificate = Request::getOrder($serviceId);

        $mainDomainValidation = $certificate['orderData']['orderData']['common_name']['name'];

        $items = reset($certificate['orderData']['items']);

        $fileName = '';
        $fileContent = '';

        foreach ($items['domains'] as $domain)
        {
            if($domain['name'] == $mainDomainValidation && ($domain['method'] == 'http' || $domain['method'] == 'https'))
            {
                $fileName = $domain['validation']['file_name'];
                $fileContent = $domain['validation']['value'];
            }
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.$fileName);
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        print $fileContent;
        exit;
    }

    public function changeApprovalMethodHTML($input, $vars = [])
    {
        $serviceId = $input['id'];
        $certificate = Request::getOrder($serviceId);
        $service = Service::where('id', $serviceId)->first();

        $configProduct = (new ProductRepository())->getProductConfiguration($service->packageid);
        $addonConfig = (new AddonModuleRepository())->getModuleConfiguration();

        $provider = strtolower($configProduct['provider']);
        $credentials = $addonConfig['credentials'][$provider];

        if($provider == 'tss' && $credentials['OperationMode'] == 'sandbox')
        {
            $credentials['PartnerCode'] = $credentials['TestPartnerCode'];
            $credentials['AuthToken'] =  $credentials['TestAuthToken'];
        }

        $available_method_validation = explode(',',$configProduct['dcv']);
        if(isset($addonConfig['sslSettings']['disableEmailValidation']) && !empty($addonConfig['sslSettings']['disableEmailValidation']))
        {
            $key = array_search('email', $available_method_validation);
            if($key !== false) unset($available_method_validation[$key]);

            $temp_available_method_validation = [];
            foreach ($available_method_validation as $method)
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
            $newOrderData = $certificate['orderData']['orderData'];
            foreach ($_POST['method'] as $domainName => $methodDomain)
            {
                $methodValidaton = $methodDomain == 'email' ? $_POST['approver'][$domainName] : $methodDomain;
                $domainChanges[] = ['domain' => ['name' => $domainName, 'validation_method' => $methodValidaton]];

                if($domainName == $newOrderData['common_name']['name'])
                {
                    $newOrderData['common_name']['validation_method'] = $methodValidaton;
                }

                foreach ($newOrderData['alternative_names'] as $key => $san)
                {
                    if($san['name'] == $domainName)
                    {
                        $newOrderData['alternative_names'][$key]['validation_method'] = $methodValidaton;
                    }
                }
            }
            try {

                foreach ($domainChanges as $domainChange) {
                    $api->changeApprovers($certificate['orderData']['order']['id'], $domainChange);
                }
                $newData = $api->getOrder($certificate['orderData']['order']['id'], $serviceId);
                $certificate['orderData']['orderData'] = $newOrderData;
                $certificate['orderData']['order'] = $newData['order'];
                $certificate['orderData']['items'] = $newData['items'];
                Request::updateOrderData($serviceId, $certificate);

            } catch (\Exception $exception) {}

            redir('action=productdetails&id='.$serviceId, 'clientarea.php');
        }

        $domains = [];
        $domains[] = $certificate['orderData']['orderData']['common_name']['name'];
        foreach ($certificate['orderData']['orderData']['alternative_names'] as $san)
        {
            $domains[] = $san['name'];
        }
        $vars['domains'] = $domains;
        $approvers = $api->getDomainEmails($domains);
        $vars['approvers'] = $this->approversToOptions($approvers);

        return ['tpl' => 'change_approval_method', 'vars' => $vars];
    }

}
