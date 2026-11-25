<?php

namespace TSSGGSModule\App\Kernel;

use TSSGGSModule\App\Database\Schema;
use TSSGGSModule\App\Database\SqlFile;
use TSSGGSModule\App\Helpers\EmailTemplates;
use TSSGGSModule\App\Libs\Support\Edition;

/**
 * What WHMCS asks an addon module for (the functions of the addon's entry file call these):
 * its description for Setup > Addon Modules, and what happens when it is activated, deactivated or upgraded.
 *
 * Activating creates the e-mail templates and the tables (app/Database/schema.sql, then the later changes in
 * App\Database\Schema); upgrading brings the tables up to date. Deactivating keeps everything: the data of the
 * orders must survive.
 */
class Lifecycle
{
    public static function config(): array
    {
        return [
            'name'        => Edition::name(),
            'description' => Edition::description(),
            'version'     => Layout::version(),
            'author'      => Edition::brand(),
            'fields'      => [],
        ];
    }

    public static function activate(): array
    {
        return self::attempt(function() {
            EmailTemplates::createTemplates();
            self::tables();
        });
    }

    public static function deactivate(): array
    {
        return ['status' => 'success'];
    }

    /** @param array $vars what WHMCS passes: "version" is the version the install had before */
    public static function upgrade(array $vars): array
    {
        return self::attempt(function() use ($vars) {
            if(empty($vars['version']))
            {
                throw new \Exception('No version specified');
            }
            self::tables();
        });
    }

    /** schema.sql creates what is missing; Schema applies the changes made since (columns, indexes, new tables) */
    protected static function tables(): void
    {
        SqlFile::run(Kernel::path('app', 'Database', 'schema.sql'));
        Schema::migrate();
    }

    protected static function attempt(callable $change): array
    {
        try
        {
            $change();

            return ['status' => 'success'];
        }
        catch(\Throwable $e)
        {
            if(function_exists('logActivity'))
            {
                \logActivity(Kernel::module() . ': ' . $e->getMessage());
            }

            return ['status' => 'error', 'description' => $e->getMessage()];
        }
    }
}
