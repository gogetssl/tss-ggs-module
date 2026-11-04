<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;

/**
 * "General" settings page (client area, messages, logging, lifecycle) with their defaults
 */
class GeneralSettings
{
    const DEFAULTS = [
        // Client area
        'clientSummary'            => true,
        'sidebarTemplates'         => '',
        'expiresSoonDays'          => 30,
        'certificatesByDomain'     => true,
        'sslTools'                 => true,
        // Downloads: the formats offered to clients
        'downloadFormat_zip'       => true,
        'downloadFormat_crt'       => true,
        'downloadFormat_ca'        => true,
        'downloadFormat_fullchain' => true,
        'downloadFormat_nginx'     => true,
        'downloadFormat_apache'    => true,
        'downloadFormat_pkcs7'     => true,
        // Messages
        'processingGuide'          => '',
        'issuedMessage'            => '',
        'certificateEmailTemplate' => '',
        'requireAgreement'         => true,
        'agreementText'            => '',    // other certificate authorities (and the fallback text)
        // subscriber agreement of each certificate authority (CertificateService::AUTHORITIES)
        'agreementUrl_sectigo'     => 'https://www.sectigo.com/legal',
        'agreementText_sectigo'    => '',
        'agreementUrl_digicert'    => 'https://www.digicert.com/legal-repository',
        'agreementText_digicert'   => '',
        'agreementUrl_gogetssl'    => 'https://www.gogetssl.com/legal/',
        'agreementText_gogetssl'   => '',
        // Logging
        'whmcsActivityLog'         => true,
        'moduleLog'                => true,
        'certificateLog'           => true,
        'internalLog'              => true,
        'apiLogMode'               => 'changes',
        'eventLog'                 => true,
        'debugServices'            => '',
        'debugUntil'               => 0,
        // Lifecycle
        'terminateCancelsOrder'    => true,
        'terminateUnpaidOnExpiry'  => true,
        // Interface
        'hideInfoMessages'         => false,
        // Orders
        'defaultWebServer'         => '',     // legacy free text (migrated TSS setting), still sent when set
        'useDefaultWebServer'      => true,   // true: clients are not asked for their web server
        // Renewal rules: days before the end of the certificate / subscription on which the service falls due
        'dueDateOffsetRegular'      => 0,
        'dueDateOffsetSubscription' => 0,
        // Pricing
        'keepMarginOnSync'         => false,
        'belowCostEmail'           => true,
        // Finance (App\Libs\FinancePolicy)
        'minProfit'                => 0,
        'termMarginStep'           => 0,
        'priceRounding'            => 'none',
        'belowCostAction'          => 'notify',
        'renewalPriceMode'         => 'never',
        'renewalPriceClientEmail'  => false,
        'renewalPriceEmailText'    => '',
        'rateSource'               => 'manual',
        'currencyBuffer'           => 0,
        'balanceAlert'             => true,
        'balanceAlertSent'         => 0,
    ];

    protected static $settings;

    public static function all()
    {
        if(self::$settings === null)
        {
            $configuration  = (new AddonModuleRepository())->getModuleConfiguration();
            $saved          = isset($configuration['generalSettings']) && is_array($configuration['generalSettings']) ? $configuration['generalSettings'] : [];
            self::$settings = array_merge(self::DEFAULTS, $saved);
        }

        return self::$settings;
    }

    public static function get($key)
    {
        $all = self::all();

        return array_key_exists($key, $all) ? $all[$key] : null;
    }

    public static function enabled($key)
    {
        $value = self::get($key);

        return $value === true || $value === 1 || $value === '1' || $value === 'on' || $value === 'true';
    }

    public static function save(array $data)
    {
        // merged into the stored settings: the settings are spread over several pages, each saves its own part
        $configuration = (new AddonModuleRepository())->getModuleConfiguration();
        $stored        = isset($configuration['generalSettings']) && is_array($configuration['generalSettings']) ? $configuration['generalSettings'] : [];
        (new AddonModuleRepository())->saveGeneralSettings(array_merge($stored, array_intersect_key($data, self::DEFAULTS)));
        self::$settings = null;
    }

    /**
     * WHMCS activity log entry - can be turned off on the settings page
     */
    public static function activity($message, $userId = 0)
    {
        if(self::enabled('whmcsActivityLog') && function_exists('logActivity'))
        {
            \logActivity(Edition::name() . ': ' . $message, (int)$userId);
        }
    }
}
