<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Update;

use ModulesGarden\TSSGGSModule\Core\Support\Facades\Config;
use WHMCS\Database\Capsule;

/**
 * What the updater knows between requests, in tbladdonmodules (module "configuration_TSSGGSModule"):
 * - moduleUpdate        last check, the newest release found (verified), its notes, errors, revoked keys
 * - moduleUpdateHistory the last updates and rollbacks (who, when, from, to, result)
 * - moduleUpdateLock    set while files are being swapped: the module's crons pause (cron/cron.php)
 * - moduleUpdateHealth  hash + expiry of the one-time token of the health check (update-health.php)
 */
class State
{
    const MODULE = 'configuration_TSSGGSModule';

    public static function get($setting = 'moduleUpdate')
    {
        $value = Capsule::table('tbladdonmodules')->where('module', self::MODULE)->where('setting', $setting)->value('value');

        return is_string($value) && $value !== '' ? (json_decode($value, true) ?: []) : [];
    }

    public static function set(array $value, $setting = 'moduleUpdate')
    {
        Capsule::table('tbladdonmodules')->updateOrInsert(['module' => self::MODULE, 'setting' => $setting], ['value' => json_encode($value)]);
    }

    public static function merge(array $values, $setting = 'moduleUpdate')
    {
        self::set(array_merge(self::get($setting), $values), $setting);
    }

    public static function forget($setting)
    {
        Capsule::table('tbladdonmodules')->where('module', self::MODULE)->where('setting', $setting)->delete();
    }

    /** The version of the files that run now */
    public static function installedVersion()
    {
        return (string)Config::get('configuration.version', '0');
    }

    /** One line in the update history (newest first, the last 20 kept) */
    public static function history(array $entry)
    {
        $history = self::get('moduleUpdateHistory');
        array_unshift($history, $entry + ['at' => date('Y-m-d H:i:s')]);
        self::set(array_slice($history, 0, 20), 'moduleUpdateHistory');
    }

    /** The module's crons pause while this is set (and not older than 15 minutes: a dead update does not stop them for good) */
    public static function locked()
    {
        $lock = self::get('moduleUpdateLock');

        return !empty($lock['since']) && time() - (int)$lock['since'] < 900;
    }
}
