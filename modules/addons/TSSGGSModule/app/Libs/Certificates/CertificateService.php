<?php

namespace TSSGGSModule\App\Libs\Certificates;

use TSSGGSModule\App\Models\RemoteProduct;
use TSSGGSModule\App\Models\Request;
use TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use TSSGGSModule\App\Repositories\Whmcs\ProductRepository;
use WHMCS\Database\Capsule;
use TSSGGSModule\App\Libs\Gateway\SSLTrustCenterApi;
use TSSGGSModule\App\Libs\ProductLines\CodeGuardService;
use TSSGGSModule\App\Libs\ProductLines\CodeSigningService;
use TSSGGSModule\App\Libs\ProductLines\DocumentSigningService;
use TSSGGSModule\App\Libs\ProductLines\LeiService;
use TSSGGSModule\App\Libs\ProductLines\PciService;
use TSSGGSModule\App\Libs\ProductLines\SmimeService;
use TSSGGSModule\App\Libs\Subscriptions\AcmeHelpers;
use TSSGGSModule\App\Libs\Subscriptions\AisHelpers;
use TSSGGSModule\App\Libs\AutoInstall\AutoInstallQueue;
use TSSGGSModule\App\Libs\Support\ClientText;
use TSSGGSModule\App\Libs\Support\GeneralSettings;

/**
 * Certificate operations shared by the admin service buttons, the WHMCS SSL module functions and the client area
 */
class CertificateService
{
    use Concerns\CertificateFiles;
    use Concerns\CertificateValidation;
    use Concerns\CertificateReissue;
    use Concerns\PrivateKeys;
    use Concerns\CertificateLifecycle;
    use Concerns\CpanelInstall;

    const CATEGORY_TLS  = 'tls';

    const CATEGORY_ACME = 'caas';

    const CATEGORY_AIS  = 'ais';

    const CATEGORY_SMIME = 'smime';

    const CATEGORY_CS    = 'cs';

    const CATEGORY_CODE_GUARD = CodeGuardService::CATEGORY;

    const CATEGORY_DS         = DocumentSigningService::CATEGORY;

    const CATEGORY_PCI        = PciService::CATEGORY;

    const CATEGORY_LEI        = LeiService::CATEGORY;

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
        if(PciService::isPciWhmcsProduct($packageId))
        {
            return self::CATEGORY_PCI;
        }
        if(LeiService::isLeiWhmcsProduct($packageId))
        {
            return self::CATEGORY_LEI;
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
            throw new \Exception(ClientText::get('certificateNotAvailable', 'The certificate is not available: the service has been cancelled or terminated.'));
        }
    }

    /**
     * Re-reads the order from the gateway when the stored copy has no validation details yet (at most every
     * REFRESH_SECONDS, so a page that keeps being reloaded does not hit the gateway each time).
     */
    const REFRESH_SECONDS = 5;

    protected static function requireOrder($serviceId)
    {
        $ssl = self::sslOrder($serviceId);
        if(!$ssl || empty($ssl->remoteid))
        {
            throw new \Exception(ClientText::get('orderNotPlacedYet', 'The SSL order has not been placed yet'));
        }

        return $ssl;
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

    const PRIVATE_KEY_REQUEST = 'privateKey';

    const RENEWAL_DAYS_MAX = 30;

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
