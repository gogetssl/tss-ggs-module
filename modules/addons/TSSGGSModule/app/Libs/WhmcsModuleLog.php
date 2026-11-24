<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

/**
 * WHMCS writes module calls to its Module Log only while its own debug logging is on (Logs > Module Log), whatever
 * the module's "WHMCS Module Log" switch says. Shown on Settings > Logs and in the Dashboard's System health.
 */
class WhmcsModuleLog
{
    /** debug logging is on in WHMCS (an unreadable setting counts as on: no false warning) */
    public static function enabled(): bool
    {
        try
        {
            return (bool)\WHMCS\Config\Setting::getValue('ModuleDebugMode');
        }
        catch(\Throwable $e)
        {
            return true;
        }
    }

    /** the WHMCS page where it is switched on (WHMCS 9 moved it under index.php?rp=) */
    public static function pageUrl(): string
    {
        $version = (string)($GLOBALS['CONFIG']['Version'] ?? '');

        return $version !== '' && version_compare($version, '9.0.0', '<') ? 'systemmodulelog.php' : 'index.php?rp=/admin/logs/module-log';
    }
}
