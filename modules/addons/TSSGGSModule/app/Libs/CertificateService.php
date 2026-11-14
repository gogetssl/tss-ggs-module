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
    const CATEGORY_SMIME = 'smime';
    const CATEGORY_CS    = 'cs';
    const CATEGORY_CODE_GUARD = CodeGuardService::CATEGORY;
    const CATEGORY_DS         = DocumentSigningService::CATEGORY;

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

        $product = (new ProductRepository())->getProductConfiguration($packageId);

        return SSLTrustCenterApi::forVendor($product['provider'], $product['category'] ?: self::CATEGORY_TLS);
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
        if(SmimeService::isSmimeWhmcsProduct($packageId))
        {
            return self::CATEGORY_SMIME;
        }
        if(CodeSigningService::isCodeSigningWhmcsProduct($packageId))
        {
            return self::CATEGORY_CS;
        }
        if(CodeGuardService::isCodeGuardWhmcsProduct($packageId))
        {
            return self::CATEGORY_CODE_GUARD;
        }
        if(DocumentSigningService::isDocumentSigningWhmcsProduct($packageId))
        {
            return self::CATEGORY_DS;
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

    /**
     * The service still owns its certificate: the WHMCS service is Active and the vendor order was not cancelled.
     * A cancelled / terminated service keeps its stored order data, which still says "active" - never offer
     * downloads, reissue or validation actions for it.
     */
    public static function serviceOpen($serviceId)
    {
        if(Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('domainstatus') !== 'Active')
        {
            return false;
        }
        $status = Request::where('serviceid', (int)$serviceId)->where('name', 'certificate')->value('status');

        return !in_array((string)$status, ['cancelled', 'rejected', 'revoked'], true);
    }

    /** An issued certificate the client may download, send or reissue */
    public static function usable($serviceId)
    {
        return self::serviceOpen($serviceId) && self::status($serviceId) === 'active';
    }

    public static function requireUsable($serviceId)
    {
        if(!self::usable($serviceId))
        {
            throw new \Exception('The certificate is not available: the service has been cancelled or terminated.');
        }
    }

    /**
     * Before a renewal replaces the stored order: keep a copy of the issued certificate, which stays valid until
     * the renewal is issued (removed again by forgetPreviousCertificate once that happens)
     */
    public static function keepPreviousCertificate($serviceId)
    {
        $row = Request::where('serviceid', (int)$serviceId)->where('name', 'certificate')->first();
        if(!$row || $row->status !== 'active' || empty($row->request))
        {
            return;
        }
        Request::where('serviceid', (int)$serviceId)->where('name', 'previousCertificate')->delete();
        Request::insert([
            'serviceid'  => (int)$serviceId,
            'name'       => 'previousCertificate',
            'request'    => $row->request,
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function forgetPreviousCertificate($serviceId)
    {
        Request::where('serviceid', (int)$serviceId)->where('name', 'previousCertificate')->delete();
    }

    /** The kept certificate while it is still valid, else [] */
    public static function previousCertificate($serviceId)
    {
        $row    = Request::where('serviceid', (int)$serviceId)->where('name', 'previousCertificate')->first();
        $stored = $row && $row->request ? (json_decode(\decrypt($row->request), true) ?: []) : [];
        $end    = $stored['orderFiles']['validity']['end'] ?? '';

        return $end !== '' && strtotime($end) > time() ? $stored : [];
    }

    /**
     * Certificate the client may download: the issued one, or during a renewal the previous one that is still valid
     */
    public static function downloadableBundle($serviceId)
    {
        if(!self::serviceOpen($serviceId))
        {
            throw new \Exception('The certificate is not available: the service has been cancelled or terminated.');
        }
        if(self::status($serviceId) === 'active')
        {
            return self::bundle($serviceId);
        }
        $previous = self::previousCertificate($serviceId);
        if($previous)
        {
            return CertificateBundle::fromOrder($previous);
        }

        throw new \Exception('The certificate has not been issued yet');
    }

    /**
     * True while a pending order lacks what the client needs to validate: no domains yet, or a domain on a file / DNS
     * method without its value. $stored = Request::getOrder() (['orderData' => .., 'orderFiles' => ..]).
     */
    public static function validationDataMissing(array $stored)
    {
        $status = isset($stored['orderData']['order']['status']) ? (string)$stored['orderData']['order']['status'] : '';
        if(in_array($status, ['active', 'cancelled', 'rejected', 'expired', 'filed'], true))
        {
            return false;
        }

        $items   = isset($stored['orderData']['items']) && is_array($stored['orderData']['items']) ? $stored['orderData']['items'] : [];
        $item    = $items ? reset($items) : [];
        $domains = isset($item['domains']) && is_array($item['domains']) ? $item['domains'] : [];
        if(!$domains)
        {
            return true;
        }

        foreach($domains as $domain)
        {
            $method = strtolower((string)($domain['method'] ?? ''));
            if(!empty($domain['approved']) || $method === '' || $method === 'email' || strpos($method, '@') !== false)
            {
                continue;
            }
            if(empty($domain['validation']['value']))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Re-reads the order from the gateway when the stored copy has no validation details yet (at most every
     * REFRESH_SECONDS, so a page that keeps being reloaded does not hit the gateway each time).
     */
    const REFRESH_SECONDS = 5;

    public static function refreshIfIncomplete($serviceId, array $stored)
    {
        if(!self::validationDataMissing($stored) || !self::serviceOpen($serviceId))
        {
            return $stored;
        }

        $row = Request::where('serviceid', (int)$serviceId)->where('name', 'certificate')->first();
        if($row && !empty($row->updated_at) && strtotime($row->updated_at) > time() - self::REFRESH_SECONDS)
        {
            return $stored;
        }

        try
        {
            self::refresh($serviceId);
        }
        catch(\Throwable $e)
        {
            return $stored;
        }

        $fresh = Request::getOrder($serviceId);

        return is_array($fresh) && $fresh ? $fresh : $stored;
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
        self::requireUsable($serviceId);
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
     * Certificate authorities refuse RSA keys under 2048 bits and EC keys under 256 bits - say so before ordering
     */
    public static function assertCsrKey($csr)
    {
        $key     = @openssl_csr_get_public_key((string)$csr);
        $details = $key ? openssl_pkey_get_details($key) : false;
        if(!$details)
        {
            return;
        }
        $minimum = $details['type'] === OPENSSL_KEYTYPE_EC ? 256 : 2048;
        if((int)$details['bits'] < $minimum)
        {
            throw new \Exception(sprintf('The CSR uses a %d-bit key. Certificate authorities need at least 2048-bit RSA or 256-bit ECC - please create a new CSR.', (int)$details['bits']));
        }
    }

    /**
     * DNS names (and IP addresses) of the subjectAltName extension inside a CSR - PHP has no function for CSR
     * extensions, so the DER is walked: OID 2.5.29.17, its OCTET STRING, the GeneralNames SEQUENCE.
     */
    public static function csrSans($csr)
    {
        $der = base64_decode(preg_replace('/-----[^-]+-----|\s+/', '', (string)$csr), true);
        $oid = "\x06\x03\x55\x1d\x11";
        if($der === false || ($pos = strpos($der, $oid)) === false)
        {
            return [];
        }

        $readLength = function($der, &$i) {
            $len = ord($der[$i++]);
            if($len & 0x80)
            {
                $bytes = $len & 0x7f;
                $len   = 0;
                for($b = 0; $b < $bytes; $b++)
                {
                    $len = ($len << 8) | ord($der[$i++]);
                }
            }

            return $len;
        };

        $i = $pos + strlen($oid);
        if(isset($der[$i]) && $der[$i] === "\x01") // optional "critical" BOOLEAN
        {
            $i += 3;
        }
        if(!isset($der[$i]) || $der[$i++] !== "\x04") // OCTET STRING
        {
            return [];
        }
        $readLength($der, $i);
        if(!isset($der[$i]) || $der[$i++] !== "\x30") // GeneralNames SEQUENCE
        {
            return [];
        }
        $end   = $readLength($der, $i) + $i;
        $names = [];
        while($i < $end && isset($der[$i]))
        {
            $tag   = ord($der[$i++]);
            $len   = $readLength($der, $i);
            $value = substr($der, $i, $len);
            $i    += $len;
            if($tag === 0x82)
            {
                $names[] = strtolower(trim($value));
            }
            elseif($tag === 0x87 && in_array($len, [4, 16], true))
            {
                $names[] = inet_ntop($value);
            }
        }

        return array_values(array_unique(array_filter($names)));
    }

    /**
     * SANs of the current order without the common name (and the free www. / bare twin of it) - what the client
     * paid for and should keep when reissuing: ['single' => [...], 'wildcard' => [...]]
     */
    public static function currentSans($serviceId)
    {
        $stored = self::stored($serviceId);
        $cn     = strtolower((string)($stored['orderData']['orderData']['common_name']['name'] ?? ''));
        $names  = [];

        foreach((array)($stored['orderData']['orderData']['alternative_names'] ?? []) as $san)
        {
            $names[] = is_array($san) ? ($san['name'] ?? ($san['domain'] ?? '')) : (string)$san;
        }
        if(!$names && !empty($stored['orderFiles']))
        {
            $crt    = AutoInstallHelpers::extractCertificate($stored['orderFiles'], $cn)['crt'];
            $parsed = $crt ? openssl_x509_parse($crt) : false;
            foreach(explode(',', (string)($parsed['extensions']['subjectAltName'] ?? '')) as $entry)
            {
                if(stripos(trim($entry), 'DNS:') === 0)
                {
                    $names[] = substr(trim($entry), 4);
                }
            }
        }

        $twin   = strpos($cn, 'www.') === 0 ? substr($cn, 4) : 'www.' . $cn;
        $result = ['single' => [], 'wildcard' => []];
        foreach(array_unique(array_map('strtolower', array_map('trim', $names))) as $name)
        {
            if($name === '' || $name === $cn || $name === $twin)
            {
                continue;
            }
            $result[strpos($name, '*.') === 0 ? 'wildcard' : 'single'][] = $name;
        }

        return $result;
    }

    /**
     * "Certificate issued" e-mail: sent by whichever sync first sees the order go from waiting (pending / processing)
     * to issued - the processing cron, the status-update cron, admin "Sync Certificate" or the client page refresh.
     * No previous status (an order migrated or imported as already issued) sends nothing.
     */
    public static function notifyIssued($serviceId, $previousStatus, $newStatus)
    {
        if($newStatus !== 'active' || !in_array((string)$previousStatus, ['pending', 'processing'], true))
        {
            return false;
        }

        try
        {
            self::sendCertificate($serviceId);

            return true;
        }
        catch(\Throwable $e)
        {
            self::event($serviceId, 'error', 'Certificate issued, but the certificate e-mail could not be sent: ' . $e->getMessage());

            return false;
        }
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

            case self::CATEGORY_CS:
                if(!CodeSigningService::cancel($serviceId, $reason))
                {
                    return false;
                }
                break;

            case self::CATEGORY_SMIME:
                if(!SmimeService::cancel($serviceId, $reason))
                {
                    return false;
                }
                break;

            case self::CATEGORY_CODE_GUARD:
                if(!CodeGuardService::cancel($serviceId, $reason))
                {
                    return false;
                }
                break;

            case self::CATEGORY_DS:
                if(!DocumentSigningService::cancel($serviceId, $reason))
                {
                    return false;
                }
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
                // WHMCS's own SSL order list shows the order as cancelled (the syncs only take "Configuration Submitted")
                Capsule::table('tblsslorders')->where('serviceid', (int)$serviceId)->update(['status' => 'Cancelled']);
                try
                {
                    AutoInstallQueue::cancelOpenTasks($serviceId, 'Order cancelled');
                }
                catch(\Throwable $e)
                {
                }
        }

        self::event($serviceId, 'info', 'Vendor order cancelled (' . $reason . ')');
        self::forgetPrivateKeys($serviceId);

        return true;
    }

    /**
     * Cancelled / terminated service: the private keys kept for it are deleted (kept key and the AutoInstall copy)
     */
    public static function forgetPrivateKeys($serviceId)
    {
        try
        {
            $deleted = Request::where('serviceid', (int)$serviceId)->where('name', self::PRIVATE_KEY_REQUEST)->delete();
            $state   = AutoInstallHelpers::get($serviceId);
            if($state && !empty($state['private_key']))
            {
                $status = $state['status'];
                unset($state['private_key']);
                AutoInstallQueue::cancelOpenTasks($serviceId, 'Service cancelled');
                AutoInstallHelpers::save($serviceId, $state, $status === AutoInstallHelpers::STATUS_INSTALLED ? $status : AutoInstallHelpers::STATUS_DISABLED);
                $deleted = true;
            }
            if($deleted)
            {
                self::event($serviceId, 'info', 'Stored private key deleted (service cancelled or terminated)');
            }
        }
        catch(\Throwable $e)
        {
        }
    }

    /**
     * Buys additional SANs on the issued certificate - the client adds the names with the next reissue
     */
    public static function addSans($serviceId, $single, $wildcard)
    {
        $ssl = self::requireOrder($serviceId);
        // always send both counts - the GoGetSSL gateway fails ("san_w_count cannot be null") when one is left out
        $data = ['single_san' => max(0, (int)$single), 'wildcard_san' => max(0, (int)$wildcard)];
        if(!$data['single_san'] && !$data['wildcard_san'])
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
        self::requireUsable($serviceId);
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
        // the names come from posted form keys: never trust them beyond the SANs that were paid for
        SanUpgrade::assertWithinLimits($serviceId, $commonName, array_keys($methods));

        // the request of the current certificate (after a renewal: the renewal's, not the first order's)
        $request      = Request::currentOrder((int)$serviceId);
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
        if($request)
        {
            Request::where('id', (int)$request->id)->update([
                'request'    => \encrypt(json_encode($requestOrder)),
                'status'     => 'pending',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $orderData  = $api->getOrder($itemId, $serviceId);
        $orderFiles = [];
        try
        {
            $orderFiles = $api->getCertificateFiles($itemId);
        }
        catch(\Exception $e)
        {
        }
        // the current certificate stays downloadable until the reissued one is out
        self::keepPreviousCertificate($serviceId);
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
            // made with the module's CSR generator: keep the new key for "Install on server"
            self::rememberGeneratedKey($serviceId, $csr);
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

    // ---- Private key of the certificate, kept (encrypted) when the module generated it ---- //

    const PRIVATE_KEY_REQUEST = 'privateKey';

    /**
     * Keeps the private key of the service encrypted, so "Install on server" does not ask the client for it.
     * Only keys the module generated (or the client pasted for an installation) are kept.
     */
    public static function storePrivateKey($serviceId, $pem)
    {
        $pem = trim((string)$pem);
        if($pem === '' || !@openssl_pkey_get_private($pem))
        {
            return;
        }
        Request::where('serviceid', (int)$serviceId)->where('name', self::PRIVATE_KEY_REQUEST)->delete();
        Request::insert([
            'serviceid'  => (int)$serviceId,
            'name'       => self::PRIVATE_KEY_REQUEST,
            'request'    => \encrypt($pem),
            'status'     => 'stored',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * A CSR the client made with the module's CSR generator in this session: keep its private key
     */
    public static function rememberGeneratedKey($serviceId, $csr)
    {
        $pem = PrivateKeyDownload::matching($serviceId, $csr);
        if($pem !== null)
        {
            self::storePrivateKey($serviceId, $pem);
        }
    }

    /**
     * The stored private key (AutoInstall or kept above) that belongs to the current certificate, '' when none does
     * (e.g. the certificate was reissued with the client's own CSR)
     */
    public static function privateKeyForCertificate($serviceId)
    {
        try
        {
            $crt = self::bundle($serviceId)->crt();
        }
        catch(\Exception $e)
        {
            return '';
        }

        $candidates = [];
        $state      = AutoInstallHelpers::get($serviceId);
        if(!empty($state['private_key']))
        {
            $candidates[] = \decrypt($state['private_key']);
        }
        $row = Request::where('serviceid', (int)$serviceId)->where('name', self::PRIVATE_KEY_REQUEST)->first();
        if($row && $row->request)
        {
            $candidates[] = \decrypt($row->request);
        }
        foreach($candidates as $pem)
        {
            $pem = trim((string)$pem);
            if($pem !== '' && @openssl_x509_check_private_key($crt, $pem))
            {
                return $pem;
            }
        }

        return '';
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
        if($privateKey === '')
        {
            $privateKey = self::privateKeyForCertificate($serviceId);
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

    // ---- Install target chosen by the administrator for services without AutoInstall ---- //

    public static function installTarget($serviceId)
    {
        $value = Request::where('serviceid', (int)$serviceId)->where('name', 'installTarget')->value('request');

        return $value === null || $value === '' ? null : (string)$value;
    }

    public static function setInstallTarget($serviceId, $domain)
    {
        $domain = strtolower(trim((string)$domain));
        if($domain === '')
        {
            Request::where('serviceid', (int)$serviceId)->where('name', 'installTarget')->delete();

            return;
        }
        if(!preg_match('/^[a-z0-9.-]+\.[a-z0-9-]+$/', $domain))
        {
            throw new \InvalidArgumentException('Install target "' . $domain . '" is not a valid domain');
        }
        Request::updateOrInsert(['serviceid' => (int)$serviceId, 'name' => 'installTarget'],
            ['request' => $domain, 'status' => 'set', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        self::event($serviceId, 'info', 'Install target set to ' . $domain . ' by the administrator');
    }

    // ---- Renewal "additional days" (days of the current certificate carried over to the renewal) ---- //

    const RENEWAL_DAYS_MAX = 30;

    /**
     * Per-service override, null = not set
     */
    public static function renewalDaysOverride($serviceId)
    {
        $value = Request::where('serviceid', (int)$serviceId)->where('name', 'renewalDays')->value('request');

        return $value === null || $value === '' ? null : (int)$value;
    }

    public static function setRenewalDaysOverride($serviceId, $days)
    {
        if($days === null || trim((string)$days) === '')
        {
            Request::where('serviceid', (int)$serviceId)->where('name', 'renewalDays')->delete();

            return;
        }
        if(!ctype_digit(trim((string)$days)) || (int)$days > self::RENEWAL_DAYS_MAX)
        {
            throw new \InvalidArgumentException('Additional days must be between 0 and ' . self::RENEWAL_DAYS_MAX);
        }
        Request::updateOrInsert(['serviceid' => (int)$serviceId, 'name' => 'renewalDays'],
            ['request' => (string)(int)$days, 'status' => 'set', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Days sent with a Sectigo renewal order: the service override, then the "Additional Days For Renewal Order" setting,
     * otherwise (and for every other CA) the days left on the current certificate
     */
    public static function renewalDays($serviceId, $remainingDays)
    {
        $packageId = (int)Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('packageid');
        if(!$packageId || !self::isSectigo($packageId))
        {
            return max(0, (int)$remainingDays);
        }

        $override = self::renewalDaysOverride($serviceId);
        if($override !== null)
        {
            return $override;
        }
        $global = (int)((new AddonModuleRepository())->getModuleConfiguration()['sslSettings']['additionalDaysForRenewalOrder'] ?? 0);

        // without a setting, the days left - but Sectigo adds no more than 30, and a renewal paid on the first day of a
        // 30-day lead counts 31
        return $global > 0 ? $global : min(self::RENEWAL_DAYS_MAX, max(0, (int)$remainingDays));
    }

    /**
     * Sectigo (Comodo) brands - the CA that honours the additional renewal days
     */
    public static function isSectigo($packageId)
    {
        $product = (new ProductRepository())->getProductConfiguration($packageId);
        $brand   = strtolower((string)$product['brand'] . ' ' . (string)RemoteProduct::where('remoteId', $product['product_id'])->where('vendor', $product['provider'])->value('brand'));

        return (bool)preg_match('/sectigo|comodo|positivessl|instantssl|essentialssl|enterprisessl/', $brand);
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

        $authority = self::authority($packageId);
        $shared    = trim((string)GeneralSettings::get('agreementText'));
        if($authority === null)
        {
            return ['name' => 'Subscriber Agreement of the certificate authority', 'url' => '', 'text' => $shared];
        }

        $own = trim((string)GeneralSettings::get('agreementText_' . $authority));

        return [
            'name' => self::AUTHORITIES[$authority]['name'] . ' Subscriber Agreement',
            'url'  => trim((string)GeneralSettings::get('agreementUrl_' . $authority)),
            'text' => $own !== '' ? $own : $shared,
        ];
    }

    /** Certificate authorities with their own subscriber agreement, and the brands that belong to each */
    const AUTHORITIES = [
        'sectigo'  => ['name' => 'Sectigo',  'brands' => ['sectigo', 'comodo', 'positivessl', 'instantssl', 'essentialssl', 'enterprisessl']],
        'digicert' => ['name' => 'DigiCert', 'brands' => ['digicert', 'geotrust', 'rapidssl', 'thawte', 'symantec']],
        'gogetssl' => ['name' => 'GoGetSSL', 'brands' => ['gogetssl']],
    ];

    /** Key of AUTHORITIES the product belongs to (from its brand), null for any other authority */
    public static function authority($packageId)
    {
        $product = (new ProductRepository())->getProductConfiguration($packageId);
        $brand   = strtolower((string)$product['brand'] . ' ' . (string)RemoteProduct::where('remoteId', $product['product_id'])->where('vendor', $product['provider'])->value('brand'));

        foreach(self::AUTHORITIES as $key => $authority)
        {
            foreach($authority['brands'] as $needle)
            {
                if(strpos($brand, $needle) !== false)
                {
                    return $key;
                }
            }
        }

        return null;
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
