<?php

namespace ModulesGarden\TSSGGSModule\App\Migration;

use WHMCS\Database\Capsule;

/**
 * Activates / deactivates a legacy addon module. Uses the WHMCS API first; when the API refuses the module type,
 * does what WHMCS does: edits tblconfiguration.ActiveAddonModules and calls the module's own hook function.
 */
class LegacyModuleControl
{
    public static function isActive(string $addon): bool
    {
        return in_array($addon, self::activeList(), true);
    }

    /** @return array ['ok' => bool, 'method' => 'api'|'direct', 'message' => string] */
    public static function deactivate(string $addon): array
    {
        if(!self::isActive($addon))
        {
            return ['ok' => true, 'method' => 'none', 'message' => 'already inactive'];
        }

        $result = self::api('DeactivateModule', $addon);
        if($result['ok'])
        {
            return $result;
        }

        self::callHook($addon, 'deactivate');
        self::saveActiveList(array_values(array_diff(self::activeList(), [$addon])));
        // WHMCS loads addon hooks from this list: an addon left in it keeps running its hooks while inactive
        self::saveList('AddonModulesHooks', array_values(array_diff(self::list('AddonModulesHooks'), [$addon])));

        return ['ok' => !self::isActive($addon), 'method' => 'direct', 'message' => 'API: ' . $result['message']];
    }

    public static function activate(string $addon): array
    {
        if(self::isActive($addon))
        {
            return ['ok' => true, 'method' => 'none', 'message' => 'already active'];
        }

        $result = self::api('ActivateModule', $addon);
        if($result['ok'])
        {
            return $result;
        }

        $list   = self::activeList();
        $list[] = $addon;
        self::saveActiveList(array_values(array_unique($list)));
        if(is_file(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . $addon . DIRECTORY_SEPARATOR . 'hooks.php'))
        {
            self::saveList('AddonModulesHooks', array_values(array_unique(array_merge(self::list('AddonModulesHooks'), [$addon]))));
        }
        self::callHook($addon, 'activate');

        return ['ok' => self::isActive($addon), 'method' => 'direct', 'message' => 'API: ' . $result['message']];
    }

    protected static function api(string $action, string $addon): array
    {
        if(!function_exists('localAPI'))
        {
            return ['ok' => false, 'method' => 'api', 'message' => 'localAPI unavailable'];
        }
        $response = \localAPI($action, ['moduleType' => 'addon', 'moduleName' => $addon]);
        $ok       = isset($response['result']) && $response['result'] === 'success';

        return ['ok' => $ok, 'method' => 'api', 'message' => $ok ? 'ok' : (string)($response['message'] ?? 'unknown error')];
    }

    protected static function activeList(): array
    {
        return self::list('ActiveAddonModules');
    }

    protected static function saveActiveList(array $list): void
    {
        self::saveList('ActiveAddonModules', $list);
    }

    protected static function list(string $setting): array
    {
        $raw = (string)Capsule::table('tblconfiguration')->where('setting', $setting)->value('value');

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    protected static function saveList(string $setting, array $list): void
    {
        Capsule::table('tblconfiguration')->where('setting', $setting)->update(['value' => implode(',', $list)]);
    }

    /**
     * Runs <addon>_activate() / <addon>_deactivate() if the module file is present (what WHMCS does on toggle)
     */
    protected static function callHook(string $addon, string $hook): void
    {
        $file = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . $addon . DIRECTORY_SEPARATOR . $addon . '.php';
        $fn   = $addon . '_' . $hook;
        try
        {
            if(is_file($file) && !function_exists($fn))
            {
                require_once $file;
            }
            if(function_exists($fn))
            {
                $fn();
            }
        }
        catch(\Throwable $e)
        {
            // the legacy module's own hook failing must not stop the migration; the list change still applies
        }
    }
}
