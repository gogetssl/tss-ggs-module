<?php

namespace TSSGGSModule\App\Libs\Support;

use TSSGGSModule\App\Models\ModuleSetting;

/**
 * Small settings of the module kept one per row (table <prefix>_ModuleSettings), e.g. what the module log records:
 *
 *   ModuleSettings::get('logs.auto_prune', 1);
 *   ModuleSettings::save(['logs.auto_prune' => 0, 'logs.types' => ['error']]);
 *
 * The values are read once per request. The settings of the admin pages are in tbladdonmodules (GeneralSettings).
 */
class ModuleSettings
{
    /** @var array|null setting => value */
    protected static $values = null;

    /** @return mixed */
    public static function get(string $name, $default = null)
    {
        $values = self::all();

        return array_key_exists($name, $values) && $values[$name] !== null ? $values[$name] : $default;
    }

    public static function save(array $settings): void
    {
        foreach($settings as $name => $value)
        {
            ModuleSetting::updateOrCreate(['setting' => (string)$name], ['value' => $value]);
            if(self::$values !== null)
            {
                self::$values[(string)$name] = $value;
            }
        }
    }

    public static function delete(string $name): void
    {
        ModuleSetting::where('setting', $name)->delete();
        unset(self::$values[$name]);
    }

    protected static function all(): array
    {
        if(self::$values === null)
        {
            try
            {
                self::$values = ModuleSetting::query()->get()->pluck('value', 'setting')->toArray();
            }
            catch(\Throwable $e)
            {
                // the table is created when the module is activated
                return [];
            }
        }

        return self::$values;
    }
}
