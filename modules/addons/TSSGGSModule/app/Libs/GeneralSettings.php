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
        // Messages
        'processingGuide'          => '',
        'issuedMessage'            => '',
        'certificateEmailTemplate' => '',
        'requireAgreement'         => true,
        'agreementText'            => '',
        // Logging
        'whmcsActivityLog'         => true,
        'moduleLog'                => true,
        // Lifecycle
        'terminateCancelsOrder'    => true,
        'terminateUnpaidOnExpiry'  => true,
        // Orders
        'defaultWebServer'         => '',
        // Renewal rules: days before the end of the certificate / subscription on which the service falls due
        'dueDateOffsetRegular'      => 0,
        'dueDateOffsetSubscription' => 0,
        // Pricing
        'keepMarginOnSync'         => false,
        'belowCostEmail'           => true,
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
        (new AddonModuleRepository())->saveGeneralSettings(array_intersect_key($data, self::DEFAULTS));
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
