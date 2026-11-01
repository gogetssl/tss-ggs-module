<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates;
use ModulesGarden\TSSGGSModule\App\Models\AcmeSubscription;
use ModulesGarden\TSSGGSModule\App\Models\AisSubscription;
use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TSSGGSModule\App\Models\Request;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\ProductRepository;
use WHMCS\Database\Capsule;

/**
 * Certificate operations shared by the admin service buttons, the WHMCS SSL module functions and the client area
 */
class CertificateService
{
    const CATEGORY_TLS  = 'tls';
    const CATEGORY_ACME = 'caas';
    const CATEGORY_AIS  = 'ais';

    /**
     * Gateway client for the service's vendor and product category
     */
    public static function api($serviceId)
    {
        $packageId = (int)Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('packageid');
        if(!$packageId)
        {
            throw new \Exception('Service #' . (int)$serviceId . ' not found');
        }

        $product     = (new ProductRepository())->getProductConfiguration($packageId);
        $credentials = (new AddonModuleRepository())->getModuleConfiguration()['credentials'][strtolower($product['provider'])] ?? [];

        if(strtolower($product['provider']) === 'tss' && ($credentials['OperationMode'] ?? '') === 'sandbox')
        {
            $credentials['PartnerCode'] = $credentials['TestPartnerCode'] ?? '';
            $credentials['AuthToken']   = $credentials['TestAuthToken'] ?? '';
        }

        if(empty($credentials['PartnerCode']))
        {
            throw new \Exception('No gateway credentials configured for ' . Helpers::vendorToDisplay($product['provider']));
        }

        return new SSLTrustCenterApi($product['provider'], $credentials['PartnerCode'], $credentials['AuthToken'], $product['category'] ?: self::CATEGORY_TLS);
    }

    public static function category($serviceId)
    {
        $packageId = (int)Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('packageid');
        if(AcmeHelpers::isAcmeWhmcsProduct($packageId))
        {
            return self::CATEGORY_ACME;
        }
        if(AisHelpers::isAisWhmcsProduct($packageId))
        {
            return self::CATEGORY_AIS;
        }

        return self::CATEGORY_TLS;
    }

    public static function sslOrder($serviceId)
    {
        return Capsule::table('tblsslorders')->where('serviceid', (int)$serviceId)->first();
    }

    /**
     * Order details + files stored by the last sync ([] when the order has not been placed yet)
     */
    public static function stored($serviceId)
    {
        $row = Request::where('serviceid', (int)$serviceId)->where('name', 'certificate')->first();
        if(!$row || empty($row->request))
        {
            return [];
        }

        return json_decode(\decrypt($row->request), true) ?: [];
    }

    public static function status($serviceId)
    {
        $stored = self::stored($serviceId);

        return isset($stored['orderData']['order']['status']) ? (string)$stored['orderData']['order']['status'] : '';
    }

    public static function bundle($serviceId)
    {
        return CertificateBundle::fromOrder(self::stored($serviceId));
    }

    /**
     * Certificate event for the service activity / the global event log
     */
    public static function event($serviceId, $type, $message, $userId = null)
    {
        if($userId === null)
        {
            $userId = (int)Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('userid');
        }

        try
        {
            AutoInstallQueue::addLog($userId, $serviceId, $type, $message);
        }
        catch(\Throwable $e)
        {
        }

        GeneralSettings::activity($message . ' - Service ID: ' . (int)$serviceId, $userId);
    }

    /**
     * Re-reads the order from the gateway (admin "Sync Certificate")
     */
    public static function refresh($serviceId)
    {
        $certificate = CertificateSyncHelpers::getCertificatesQuery()->where('tblhosting.id', (int)$serviceId)->first();
        if(!$certificate)
        {
            throw new \Exception('The certificate can be synchronized only for an Active service with the "Configuration Submitted" SSL order status');
        }

        return CertificateSyncHelpers::syncCertificate($certificate->toArray(), (new AddonModuleRepository())->getModuleConfiguration());
    }

    protected static function requireOrder($serviceId)
    {
        $ssl = self::sslOrder($serviceId);
        if(!$ssl || empty($ssl->remoteid))
        {
            throw new \Exception('The SSL order has not been placed yet');
        }

        return $ssl;
    }

    public static function resendApproverEmail($serviceId)
    {
        $ssl = self::requireOrder($serviceId);
        self::api($serviceId)->resendValidationEmail((int)$ssl->remoteid);
        self::event($serviceId, 'info', 'Validation email resent');
    }

    public static function revalidate($serviceId)
    {
        $ssl = self::requireOrder($serviceId);
        self::api($serviceId)->recheckValidation((int)$ssl->remoteid);
        self::event($serviceId, 'info', 'Domain validation recheck requested');

        return self::refresh($serviceId);
    }

    /**
     * E-mails the certificate to the client; the template can be chosen on the settings page
     */
    public static function sendCertificate($serviceId)
    {
        $stored = self::stored($serviceId);
        $bundle = CertificateBundle::fromOrder($stored);
        $cn     = $stored['orderData']['orderData']['common_name']['name'] ?? '';

        $files = [];
        $dir   = self::attachmentsDir();
        foreach(['crt' => $bundle->crt(), 'ca-bundle' => $bundle->ca()] as $ext => $content)
        {
            if(trim($content) === '')
            {
                continue;
            }
            $name = (int)$serviceId . '_' . CertificateBundle::fileName($cn) . '.' . $ext;
            if($dir !== null)
            {
                file_put_contents($dir . DIRECTORY_SEPARATOR . $name, $content);
                $files[] = ['displayname' => CertificateBundle::fileName($cn) . '.' . $ext, 'filename' => $name];
            }
            else
            {
                $path = tempnam(sys_get_temp_dir(), 'tssggs');
                file_put_contents($path, $content);
                $files[] = ['displayname' => CertificateBundle::fileName($cn) . '.' . $ext, 'path' => $path];
            }
        }

        $fields = [
            'domain'          => $cn,
            'ssl_certyficate' => nl2br($bundle->ca()),
            'crt_code'        => nl2br($bundle->crt()),
        ];

        $template = (string)GeneralSettings::get('certificateEmailTemplate');
        try
        {
            if($template !== '')
            {
                \sendMessage($template, (int)$serviceId, $fields, false, $files);
            }
            else
            {
                EmailTemplates::sendEmail(EmailTemplates::SEND_CERTIFICATE_TEMPLATE, (int)$serviceId, $fields, $files);
            }
        }
        finally
        {
            foreach($files as $file)
            {
                if(isset($file['path']))
                {
                    @unlink($file['path']);
                }
            }
        }

        self::event($serviceId, 'info', 'Certificate sent to the client by email');
    }

    /**
     * Local folder of the WHMCS e-mail attachments storage (null when attachments are stored elsewhere)
     */
    protected static function attachmentsDir()
    {
        $schema = Capsule::schema();
        if(!$schema->hasTable('tblfileassetsettings') || !$schema->hasTable('tblstorageconfigurations'))
        {
            return null;
        }

        $setting = Capsule::table('tblfileassetsettings')->where('asset_type', 'email_attachments')->first();
        $storage = $setting ? Capsule::table('tblstorageconfigurations')->where('id', $setting->storageconfiguration_id)->first() : null;
        $config  = $storage ? json_decode($storage->settings, true) : [];

        return !empty($config['local_path']) && is_dir($config['local_path']) ? $config['local_path'] : null;
    }

    /**
     * Cancels the vendor order of any category (Terminate, admin "Cancel order")
     */
    public static function cancel($serviceId, $reason)
    {
        switch(self::category($serviceId))
        {
            case self::CATEGORY_ACME:
                $subscription = AcmeSubscription::getByServiceId($serviceId);
                if(!$subscription || (int)$subscription->acme_id <= 0 || in_array($subscription->status, ['cancelled', 'terminated'], true))
                {
                    return false;
                }
                self::api($serviceId)->cancelItem((int)$subscription->acme_id, $reason, self::CATEGORY_ACME);
                $subscription->status       = 'cancelled';
                $subscription->cancelled_at = date('Y-m-d H:i:s');
                $subscription->save();
                break;

            case self::CATEGORY_AIS:
                $subscription = AisSubscription::getByServiceId($serviceId);
                if(!$subscription || (int)$subscription->ais_item_id <= 0 || in_array($subscription->status, ['cancelled', 'terminated'], true))
                {
                    return false;
                }
                self::api($serviceId)->cancelAisSubscription((int)$subscription->ais_item_id, $reason);
                $subscription->status = 'cancelled';
                $subscription->save();
                break;

            default:
                $ssl = self::sslOrder($serviceId);
                if(!$ssl || empty($ssl->remoteid) || in_array(self::status($serviceId), AutoInstallHelpers::FINAL_ORDER_STATUSES, true))
                {
                    return false;
                }
                self::api($serviceId)->cancelItem((int)$ssl->remoteid, $reason);
                Request::where('serviceid', (int)$serviceId)->where('name', 'addOrder')->update(['status' => 'cancelled']);
                Request::where('serviceid', (int)$serviceId)->where('name', 'certificate')->update(['status' => 'cancelled']);
                try
                {
                    AutoInstallQueue::cancelOpenTasks($serviceId, 'Order cancelled');
                }
                catch(\Throwable $e)
                {
                }
        }

        self::event($serviceId, 'info', 'Vendor order cancelled (' . $reason . ')');

        return true;
    }

    /**
     * Buys additional SANs on the issued certificate - the client adds the names with the next reissue
     */
    public static function addSans($serviceId, $single, $wildcard)
    {
        $ssl = self::requireOrder($serviceId);
        $data = array_filter(['single_san' => max(0, (int)$single), 'wildcard_san' => max(0, (int)$wildcard)]);
        if(!$data)
        {
            return false;
        }

        self::api($serviceId)->addSan((int)$ssl->remoteid, $data);
        self::event($serviceId, 'success', 'Additional SANs ordered: ' . (int)$single . ' single, ' . (int)$wildcard . ' wildcard');

        return true;
    }

    /**
     * Reissues the certificate with a new CSR. $methods: [domain => 'http'|'https'|'dns'|approver email], the common
     * name first. When the CSR uses the private key stored for AutoInstall, the reissued certificate is installed
     * automatically again; any other key turns AutoInstall off for the service.
     */
    public static function reissue($serviceId, $csr, array $methods)
    {
        $ssl     = self::requireOrder($serviceId);
        $subject = @openssl_csr_get_subject($csr);
        if(!$subject || empty($subject['CN']))
        {
            throw new \Exception('The CSR is not valid');
        }
        $commonName = $subject['CN'];
        if(empty($methods[$commonName]))
        {
            throw new \Exception('No validation method for ' . $commonName);
        }

        $request      = Request::where('serviceid', (int)$serviceId)->where('name', 'addOrder')->first();
        $requestOrder = $request && $request->request ? (json_decode(\decrypt($request->request), true) ?: []) : [];

        $sans = [];
        foreach($methods as $domain => $method)
        {
            if($domain !== $commonName)
            {
                $sans[] = ['name' => $domain, 'validation_method' => $method];
            }
        }

        $api     = self::api($serviceId);
        $reissue = $api->reissueCertificate((int)$ssl->remoteid, [
            'csr'                         => trim($csr),
            'common_name'                 => ['name' => $commonName, 'validation_method' => $methods[$commonName]],
            'existing_alternative_names'  => isset($requestOrder['alternative_names']) ? $requestOrder['alternative_names'] : [],
            'submitted_alternative_names' => $sans,
        ]);
        $itemId = SSLTrustCenterApi::getItemId($reissue, (int)$ssl->remoteid);

        $requestOrder['csr']               = trim($csr);
        $requestOrder['common_name']       = ['name' => $commonName, 'validation_method' => $methods[$commonName]];
        $requestOrder['alternative_names'] = $sans;
        Request::where('serviceid', (int)$serviceId)->where('name', 'addOrder')->update([
            'request'    => \encrypt(json_encode($requestOrder)),
            'status'     => 'pending',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $orderData  = $api->getOrder($itemId, $serviceId);
        $orderFiles = [];
        try
        {
            $orderFiles = $api->getCertificateFiles($itemId);
        }
        catch(\Exception $e)
        {
        }
        Request::where('serviceid', (int)$serviceId)->where('name', 'certificate')->update([
            'request'    => \encrypt(json_encode(['orderData' => $orderData, 'orderFiles' => $orderFiles])),
            'status'     => 'pending',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        Capsule::table('tblsslorders')->where('serviceid', (int)$serviceId)->update(['remoteid' => $itemId]);
        Capsule::table('tblhosting')->where('id', (int)$serviceId)->update(['domain' => $commonName]);

        $keepAutoInstall = self::csrUsesAutoInstallKey($serviceId, $csr);
        try
        {
            if($keepAutoInstall)
            {
                self::queueAutoInstallAfterReissue($serviceId, $itemId, $methods[$commonName], count(array_filter(array_keys($methods), function($d) { return strpos($d, '*.') === 0; })) > 0);
            }
            else
            {
                AutoInstallHelpers::onManualReissue($serviceId);
            }
        }
        catch(\Throwable $e)
        {
        }

        self::event($serviceId, 'success', '[Reissue] Certificate reissue submitted for ' . $commonName . ' (' . count($methods) . ' domain(s))'
            . ($keepAutoInstall ? ' - it will be installed automatically once issued' : ''));

        return $itemId;
    }

    /**
     * CSR made from the AutoInstall private key (same public key)
     */
    public static function csrUsesAutoInstallKey($serviceId, $csr)
    {
        $state = AutoInstallHelpers::get($serviceId);
        if(empty($state['private_key']) || $state['status'] === AutoInstallHelpers::STATUS_DISABLED)
        {
            return false;
        }

        $key       = @openssl_pkey_get_private(\decrypt($state['private_key']));
        $csrPublic = @openssl_csr_get_public_key($csr);
        if(!$key || !$csrPublic)
        {
            return false;
        }

        return openssl_pkey_get_details($key)['key'] === openssl_pkey_get_details($csrPublic)['key'];
    }

    /**
     * New CSR for a reissue, signed with the private key stored for AutoInstall
     */
    public static function csrFromAutoInstallKey($serviceId, $commonName)
    {
        $state = AutoInstallHelpers::get($serviceId);
        $key   = !empty($state['private_key']) ? @openssl_pkey_get_private(\decrypt($state['private_key'])) : false;
        if(!$key)
        {
            throw new \Exception('No private key is stored for the automatic installation of this certificate');
        }

        $csr = openssl_csr_new(['commonName' => $commonName], $key, ['digest_alg' => 'sha256']);
        if(!$csr || !openssl_csr_export($csr, $pem))
        {
            throw new \Exception('The CSR could not be generated');
        }

        return $pem;
    }

    protected static function queueAutoInstallAfterReissue($serviceId, $itemId, $method, $isWildcard)
    {
        $state  = AutoInstallHelpers::get($serviceId);
        $userId = (int)Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('userid');
        $task   = [
            'domain'       => $state['domain'],
            'api_order_id' => (int)$itemId,
            'is_reissue'   => true,
            'is_wildcard'  => $isWildcard || !empty($state['is_wildcard']),
        ];
        if($method === 'dns')
        {
            $task['dns_check_start'] = time();
        }

        AutoInstallQueue::cancelOpenTasks($serviceId, 'Certificate reissued');
        AutoInstallQueue::addTask($userId, $serviceId, 'validate', $task);
        $state['dcv_method'] = strpos($method, '@') !== false ? 'email' : $method;
        $state['last_error'] = '';
        AutoInstallHelpers::save($serviceId, $state, AutoInstallHelpers::STATUS_AWAITING);
    }

    /**
     * Changes the validation method / approver email of pending domains: [domain => 'http'|'https'|'dns'|email]
     */
    public static function changeValidation($serviceId, array $methods)
    {
        $ssl    = self::requireOrder($serviceId);
        $api    = self::api($serviceId);
        $stored = self::stored($serviceId);

        foreach($methods as $domain => $method)
        {
            $api->changeApprovers((int)$ssl->remoteid, ['domain' => ['name' => $domain, 'validation_method' => $method]]);
            if(isset($stored['orderData']['orderData']['common_name']['name']) && $stored['orderData']['orderData']['common_name']['name'] === $domain)
            {
                $stored['orderData']['orderData']['common_name']['validation_method'] = $method;
            }
            foreach(isset($stored['orderData']['orderData']['alternative_names']) ? $stored['orderData']['orderData']['alternative_names'] : [] as $i => $san)
            {
                if(($san['name'] ?? '') === $domain)
                {
                    $stored['orderData']['orderData']['alternative_names'][$i]['validation_method'] = $method;
                }
            }
        }

        $fresh = $api->getOrder((int)$ssl->remoteid, $serviceId);
        $stored['orderData']['order'] = $fresh['order'];
        $stored['orderData']['items'] = $fresh['items'];
        Request::updateOrderData($serviceId, $stored);

        self::event($serviceId, 'info', '[Validation] Validation method changed: ' . implode(', ', array_map(function($d, $m) {
            return $d . ' => ' . $m;
        }, array_keys($methods), $methods)));
    }

    /**
     * Installs the issued certificate on the client's cPanel account hosting $domain. The private key is the one
     * stored for AutoInstall or the one the client pastes. The service is enrolled in AutoInstall afterwards,
     * so reissues and renewals are installed automatically.
     */
    public static function installOnCpanel($serviceId, $domain, $privateKey = '')
    {
        $domain = strtolower(trim((string)$domain));
        $userId = (int)Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('userid');
        $bundle = self::bundle($serviceId);
        $state  = AutoInstallHelpers::get($serviceId) ?: [];

        $privateKey = trim((string)$privateKey);
        if($privateKey === '' && !empty($state['private_key']))
        {
            $privateKey = trim((string)\decrypt($state['private_key']));
        }
        if($privateKey === '')
        {
            throw new \Exception('The private key of this certificate is required');
        }
        if(!@openssl_x509_check_private_key($bundle->crt(), $privateKey))
        {
            throw new \Exception('The private key does not match the certificate');
        }

        $cpanelService = Cpanel::getServiceByDomain($userId, $domain);
        if($cpanelService === false)
        {
            throw new \Exception('No cPanel hosting account with the domain ' . $domain . ' was found');
        }

        $cpanel = new Cpanel();
        $cpanel->setService($cpanelService);
        $cpanel->installSSL($cpanelService->user, $domain, $bundle->crt(), $privateKey, $bundle->ca());

        $stored = self::stored($serviceId);
        $state  = array_merge($state, [
            'install_type'       => 'autoinstall',
            'domain'             => $domain,
            'common_name'        => $stored['orderData']['orderData']['common_name']['name'] ?? $domain,
            'private_key'        => \encrypt($privateKey),
            'installed_crt_hash' => sha1(trim($bundle->crt())),
            'installed_at'       => date('Y-m-d H:i:s'),
            'valid_till'         => $stored['orderFiles']['validity']['end'] ?? '',
            'last_error'         => '',
        ]);
        if(!AutoInstallHelpers::get($serviceId))
        {
            AutoInstallHelpers::create($serviceId, $state);
        }
        AutoInstallHelpers::save($serviceId, $state, AutoInstallHelpers::STATUS_INSTALLED);

        self::event($serviceId, 'success', '[Install] Certificate installed on cPanel - domain: ' . $domain . ' | server: ' . $cpanelService->hostname, $userId);
    }

    /**
     * Subscriber agreement the client accepts before the order is placed, null when not required.
     * The link points to the legal repository of the product's certificate authority.
     */
    public static function agreement($packageId)
    {
        if(!GeneralSettings::enabled('requireAgreement'))
        {
            return null;
        }

        $product = (new ProductRepository())->getProductConfiguration($packageId);
        $brand   = strtolower((string)$product['brand'] . ' ' . (string)RemoteProduct::where('remoteId', $product['product_id'])->where('vendor', $product['provider'])->value('brand'));

        $authorities = [
            'Sectigo'  => ['sectigo', 'comodo', 'positivessl', 'instantssl', 'essentialssl', 'enterprisessl', 'trustedsite'],
            'DigiCert' => ['digicert', 'geotrust', 'rapidssl', 'thawte', 'symantec', 'encryption everywhere'],
            'GoGetSSL' => ['gogetssl'],
        ];
        $urls = [
            'Sectigo'  => 'https://www.sectigo.com/legal',
            'DigiCert' => 'https://www.digicert.com/legal-repository',
            'GoGetSSL' => 'https://www.gogetssl.com/legal/',
        ];

        $name = '';
        foreach($authorities as $authority => $needles)
        {
            foreach($needles as $needle)
            {
                if(strpos($brand, $needle) !== false)
                {
                    $name = $authority;
                    break 2;
                }
            }
        }

        return [
            'name' => $name !== '' ? $name . ' Subscriber Agreement' : 'Subscriber Agreement of the certificate authority',
            'url'  => $name !== '' ? $urls[$name] : '',
            'text' => trim((string)GeneralSettings::get('agreementText')),
        ];
    }

    /**
     * Admin/tech contacts and organisation sent with the order
     */
    public static function contacts($serviceId)
    {
        $row = Request::where('serviceid', (int)$serviceId)->where('name', 'addOrder')->first();
        $order = $row && $row->request ? (json_decode(\decrypt($row->request), true) ?: []) : [];

        return isset($order['contacts']) && is_array($order['contacts']) ? $order['contacts'] : [];
    }
}
