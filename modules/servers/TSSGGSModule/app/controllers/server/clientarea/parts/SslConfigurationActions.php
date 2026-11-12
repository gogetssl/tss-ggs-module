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
 * SSL certificate order form (configuration) and its submission.
 * Part of the client area controller (clientarea\home): the methods keep their names, so the page URLs are unchanged.
 */
trait SslConfigurationActions
{
    private function getSslApiForProduct($packageId)
    {
        $configProduct = (new ProductRepository())->getProductConfiguration($packageId);
        $addonConfig   = (new AddonModuleRepository())->getModuleConfiguration();

        return SSLTrustCenterApi::forVendor($configProduct['provider'], $configProduct['category']);
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
        $file      = (defined('ROOTDIR') ? ROOTDIR : dirname(__DIR__, 8)) . DS . 'resources' . DS . 'country' . DS . 'dist.countries.json';
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
}
