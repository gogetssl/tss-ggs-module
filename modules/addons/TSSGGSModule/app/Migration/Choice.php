<?php

namespace ModulesGarden\TSSGGSModule\App\Migration;

use WHMCS\Database\Capsule;

/**
 * The admin's decision per legacy module ("migrate" or "skip"), taken in the wizard and stored with the
 * module configuration (tbladdonmodules, module configuration_TSSGGSModule, setting "migration").
 */
class Choice
{
    const MIGRATE = 'migrate';
    const SKIP    = 'skip';

    const MODULE  = 'configuration_TSSGGSModule';
    const SETTING = 'migration';

    public static function all(): array
    {
        $raw = Capsule::table('tbladdonmodules')->where('module', self::MODULE)->where('setting', self::SETTING)->value('value');

        return $raw ? (json_decode($raw, true) ?: []) : [];
    }

    public static function get(string $source): ?string
    {
        return self::all()[$source] ?? null;
    }

    public static function set(string $source, string $choice): void
    {
        $all          = self::all();
        $all[$source] = $choice;
        $row          = Capsule::table('tbladdonmodules')->where('module', self::MODULE)->where('setting', self::SETTING)->first();
        if($row)
        {
            Capsule::table('tbladdonmodules')->where('id', $row->id)->update(['value' => json_encode($all)]);
        }
        else
        {
            Capsule::table('tbladdonmodules')->insert(['module' => self::MODULE, 'setting' => self::SETTING, 'value' => json_encode($all)]);
        }
    }

    /** sources the admin chose to migrate */
    public static function toMigrate(): array
    {
        return array_keys(array_filter(self::all(), function($c) { return $c === self::MIGRATE; }));
    }
}
