<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use WHMCS\User\Admin\Permission;

/**
 * WHMCS writes module calls to its Module Log only while its own debug logging is on (Logs > Module Log), whatever
 * the module's "WHMCS Module Log" switch says. Shown on Settings > Logs and in the Dashboard's System health; an
 * administrator allowed to use the WHMCS Module Log page can switch it on from Settings > Logs (never automatically:
 * the setting is WHMCS-wide and logs every module).
 */
class WhmcsModuleLog
{
    /** the WHMCS permission of the Module Log page, where WHMCS itself switches it on */
    const PERMISSION = 'View Module Debug Log';

    /** session key of the result shown on Settings > Logs after the button */
    const SESSION = 'tssggs_whmcs_module_log';

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

    public static function canEnable(): bool
    {
        try
        {
            return Permission::currentAdminHasPermissionName(self::PERMISSION);
        }
        catch(\Throwable $e)
        {
            return false;
        }
    }

    /** switches debug logging on in WHMCS, as its own Module Log page does, and says so in the activity log */
    public static function enable()
    {
        if(!self::canEnable())
        {
            throw new \RuntimeException('Your administrator role is missing the WHMCS permission "' . self::PERMISSION . '".');
        }
        \WHMCS\Config\Setting::setValue('ModuleDebugMode', 'on');
        \logActivity('Module Log Enabled (from the SSL module, Settings > Logs)');
    }
}
