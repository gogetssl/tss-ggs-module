<?php

namespace TSSGGSModule\Server\app\Client\Parts;

use TSSGGSModule\Server\app\models\whmcs\Client;
use TSSGGSModule\Server\app\models\whmcs\Product;
use TSSGGSModule\Server\app\models\whmcs\SSL;
use TSSGGSModule\Server\app\repository\whmcs\Domain;
use TSSGGSModule\Server\app\repository\whmcs\ProductConfigOptions;
use TSSGGSModule\Server\Text;
use TSSGGSModule\App\Libs\AutoInstall\AutoInstallHelpers;
use TSSGGSModule\App\Libs\Certificates\CertificateService;
use TSSGGSModule\App\Libs\Gateway\SSLTrustCenterApi;
use TSSGGSModule\App\Models\Request;
use TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use TSSGGSModule\App\Repositories\Whmcs\ProductRepository;

/**
 * Reissue of an SSL certificate (three steps) and the CSR helpers it uses.
 * Part of SslCertificatePages (used there as a trait).
 */
trait SslReissueActions
{
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
        $countries = file_get_contents((defined('ROOTDIR') ? ROOTDIR : dirname(__DIR__, 6)) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'country' . DIRECTORY_SEPARATOR . 'dist.countries.json');

        $configuration = (new AddonModuleRepository())->getModuleConfiguration();

        $clientData = $input['params']['model']->client->toArray();

        // Default CSR Country: the fixed one from the settings, else the client's own country
        $defaultCountry = strtoupper(trim((string)($configuration['sslSettings']['defaultCsrCountry'] ?? '')));
        if(!preg_match('/^[A-Z]{2}$/', $defaultCountry))
        {
            $defaultCountry = strtoupper(trim((string)($clientData['country'] ?? '')));
        }
        $configuration['sslSettings']['defaultCsrCountry'] = $defaultCountry;
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

        // a certificate for public IPs (App\Libs\Certificates\IpCertificate): CSR without common name, the main name stays and
        // uses one of the product's SANs - the field takes the other names only
        $vars['ipProduct'] = \TSSGGSModule\App\Libs\Certificates\IpCertificate::isProduct($params['packageid']);
        if($vars['ipProduct'])
        {
            $vars['sans']          = max(0, (int)$vars['sans'] - 1);
            $vars['sans_wildcard'] = 0;
            $vars['ip_csr_hint']   = sprintf(Text::get('reissueIpCsrHint'), (string)$input['params']['domain']);
        }

        // Reissue with the AutoInstall key keeps the automatic installation; more SANs can be bought before the reissue
        $autoInstall             = AutoInstallHelpers::get($serviceId);
        $vars['autoinstall_key'] = !empty($autoInstall['private_key']) && $autoInstall['status'] !== AutoInstallHelpers::STATUS_DISABLED;
        $sanOptions              = ProductConfigOptions::get($params['packageid'], $serviceId, $params['model']->billingcycle);
        $vars['buy_sans_url']    = $sanOptions ? 'upgrade.php?type=configoptions&id=' . (int)$serviceId : '';
        $vars['lang_san']          = sprintf(Text::get('sans_additional'), $vars['sans']);
        $vars['lang_san_wildcard'] = sprintf(Text::get('sans_additional_wildcard'), $vars['sans_wildcard']);

        $vars['errors']            = isset($_SESSION['mod-error']) ? $_SESSION['mod-error'] : false;
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

        if(isset($_SESSION['mod-error']))
        {
            unset($_SESSION['mod-error']);
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
            $mainName = (string)($decode['CN'] ?? '');
            // a certificate for public IPs (App\Libs\Certificates\IpCertificate): CSR without common name, the main name stays
            $ipCertificate = \TSSGGSModule\App\Libs\Certificates\IpCertificate::isProduct($input['params']['packageid']);
            if($ipCertificate)
            {
                if($mainName !== '')
                {
                    throw new \Exception(Text::get('reissueIpCsrHasCn'));
                }
                $mainName = strtolower(trim((string)$input['params']['domain']));
                // every name uses a SAN of the product, the main name too
                $count = count(array_filter(preg_split('/[\s,;]+/', (string)$fields['sans_domains'])));
                $limit = (int)($input['params']['configoptions']['sans'] ?? 0) + (int)$input['params']['configoption7'];
                if($count + 1 > max(1, $limit))
                {
                    throw new \Exception(sprintf(Text::get('using_max_san'), max(0, $limit - 1)));
                }
            }
            $domains = $this->validateDomains($mainName, $fields, $input['params']);

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
            // the methods offered on the page: the product's (dns-cname / dns-txt shown as DNS), file validation only
            // for IP certificates
            $dcvMethods = array_values(array_intersect(['email', 'http', 'https', 'dns'], array_map(function($method) {
                $method = strtolower(trim((string)$method));
                return in_array($method, ['dns-cname', 'dns-txt']) ? 'dns' : $method;
            }, $available_method_validation)));
            if($ipCertificate)
            {
                $dcvMethods = array_values(array_intersect($dcvMethods ?: ['http', 'https'], ['http', 'https'])) ?: ['http', 'https'];
            }
            $vars['dcv_methods'] = $dcvMethods ?: ['email', 'http', 'https', 'dns'];

            $api               = SSLTrustCenterApi::forVendor($configProduct['provider'], $configProduct['category']);
            // IP addresses have no approver mailboxes (file validation only)
            $approvers         = $ipCertificate ? array_map(function($name) { return ['domain' => $name, 'emails' => []]; }, $domains) : $api->getDomainEmails($domains);
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
            $_SESSION['mod-error']          = $e->getMessage();
            redir('action=productdetails&id=' . $input['id'] . '&mod-action=reissueSSL', 'clientarea.php');

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

            // CN first - for a certificate for public IPs (CSR without CN) the current main name
            $commonName = (string)(openssl_csr_get_subject($input['csr'])['CN'] ?? '');
            if($commonName === '' && \TSSGGSModule\App\Libs\Certificates\IpCertificate::isProduct($input['params']['packageid']))
            {
                $commonName = strtolower(trim((string)$input['params']['domain']));
            }
            if(isset($methods[$commonName]))
            {
                $methods = [$commonName => $methods[$commonName]] + $methods;
            }

            CertificateService::reissue($serviceId, $input['csr'], $methods);

            $_SESSION['mod-success'] = Text::get('reissue_success');
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
            throw new \Exception(Text::get('csr_error'));
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
                throw new \Exception(sprintf(Text::get('using_max_san'), $countSan));
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
                throw new \Exception(sprintf(Text::get('using_max_san_wildcard'), $countSanWildCard));
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

            // a certificate for public IPs (App\Libs\Certificates\IpCertificate): the CSR has no common name
            $ipProduct = \TSSGGSModule\App\Libs\Certificates\IpCertificate::isProduct($input['params']['packageid'] ?? 0);
            $csr = openssl_csr_new(array_filter([
                'countryName' => strtoupper((string)$input['country']),
                'stateOrProvinceName' => (string)$input['state'],
                'localityName' => (string)$input['locality'],
                'organizationName' => (string)$input['organization'],
                'organizationalUnitName' => (string)$input['unit_name'],
                'commonName' => $ipProduct ? '' : (string)$input['common_name'],
                'emailAddress' => (string)$input['email_address'],
            ], function($v) { return $v !== ''; }), $privateKey);

            if(!$csr)
            {
                return ['json' => ['status' => 'error', 'message' => Text::get('error_csr_generate')]];
            }

            openssl_csr_export($csr, $csrOut);

            return ['json' => ['status' => 'success', 'data' => ['csr' => $csrOut, 'private_key' => \TSSGGSModule\App\Libs\Certificates\PrivateKeyDownload::store((int)$input['id'], $pKeyOut)]]];

        }
        catch(\Exception $e)
        {

            return ['json' => ['status' => 'error', 'message' => $e->getMessage()]];

        }

    }
}
