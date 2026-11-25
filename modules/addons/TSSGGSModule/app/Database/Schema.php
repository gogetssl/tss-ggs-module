<?php

namespace ModulesGarden\TSSGGSModule\App\Database;

use ModulesGarden\TSSGGSModule\App\Libs\Support\Edition;
use WHMCS\Database\Capsule;

/**
 * The module's database changes after schema.sql, in one numbered list. schema.sql creates the base tables on
 * activation and upgrade; every later change (a table, a column, an index, a dropped table) is a step here.
 *
 * migrate() runs the steps newer than the version stored in tbladdonmodules (setting "schema_version") and stores
 * the new version. It runs from every entry point - the addon (activate, upgrade, configuration, pages), the cron and
 * the server module - so an install updated by copying files, where WHMCS never ran the addon upgrade, is brought up
 * to date as well. Once an install is current it costs one query per request.
 *
 * Every step must be idempotent: installs that ran the older per-feature checks already have some of the changes,
 * and two processes may migrate at the same moment. A new change = a new step at the end with the next number;
 * never edit or renumber a released step.
 */
class Schema
{
    /** version => [method, what it does] */
    const STEPS = [
        1 => ['requestsServiceIndex', 'Requests: index on serviceid'],
        2 => ['dropDemonTask', 'drop DemonTask (client session ids of a removed task runner, 1.7.1)'],
        3 => ['subscriptionAccountStatus', 'ACME / AIS subscriptions: account_status'],
        4 => ['aisInstallColumns', 'AIS subscriptions: install method and ACME account columns'],
        5 => ['clientMarkupsTable', 'ClientMarkups table'],
        6 => ['catalogueChangesTable', 'CatalogueChanges table'],
        7 => ['autoInstallReportTable', 'AutoInstallReport table'],
        8 => ['autoInstallQueueTables', 'AutoInstallQueue and AutoInstallLogs tables'],
    ];

    const SETTING = 'schema_version';

    /** the version this request found or reached (null: not read yet) */
    protected static $version = null;

    /** @return string[] what was done (empty when the install was current) */
    public static function migrate(): array
    {
        $latest = max(array_keys(self::STEPS));
        if(self::$version === null)
        {
            self::$version = (int)Capsule::table('tbladdonmodules')->where('module', self::module())->where('setting', self::SETTING)->value('value');
        }
        if(self::$version >= $latest)
        {
            return [];
        }
        // not installed yet: activation runs schema.sql first and calls migrate() again afterwards (Activate\After)
        if(!self::hasTable(Edition::table('Requests')))
        {
            return [];
        }

        $done = [];
        foreach(self::STEPS as $number => [$method, $what])
        {
            if($number <= self::$version)
            {
                continue;
            }
            if(self::$method() !== false)
            {
                $done[] = $number . ': ' . $what;
            }
            // stored after each step: a failure later on does not repeat the finished ones
            self::$version = $number;
            Capsule::table('tbladdonmodules')->updateOrInsert(['module' => self::module(), 'setting' => self::SETTING], ['value' => (string)$number]);
        }
        self::afterUpgrade($done);

        return $done;
    }

    /** the version this install has reached */
    public static function version(): int
    {
        self::migrate();

        return (int)self::$version;
    }

    /** tbladdonmodules name of the module's own settings (the build renames TSSGGSModule to the edition's folder) */
    protected static function module(): string
    {
        return 'configuration_TSSGGSModule';
    }

    // ---- steps: true = changed something, false = nothing to do (already there) ---- //

    protected static function requestsServiceIndex(): bool
    {
        $table = Edition::table('Requests');
        if(!self::hasTable($table) || self::hasIndexOn($table, 'serviceid'))
        {
            return false; // not installed yet (schema.sql creates it with the index) or done
        }
        Capsule::statement('ALTER TABLE ' . self::quote($table) . ' ADD INDEX `serviceid` (`serviceid`)');

        return true;
    }

    protected static function dropDemonTask(): bool
    {
        $table = Edition::table('DemonTask');
        if(!self::hasTable($table))
        {
            return false;
        }
        Capsule::schema()->drop($table);

        return true;
    }

    protected static function subscriptionAccountStatus(): bool
    {
        $changed = false;
        foreach(['AcmeSubscriptions', 'AisSubscriptions'] as $name)
        {
            $changed = self::addColumns(Edition::table($name), ['account_status' => ['string', 32]]) || $changed;
        }

        return $changed;
    }

    protected static function aisInstallColumns(): bool
    {
        return self::addColumns(Edition::table('AisSubscriptions'), [
            'install_method'  => ['string', 32],
            'acme_account_id' => ['string', 255],
            'eab_kid'         => ['string', 255],
            'eab_hmac_key'    => ['text'],
            'server_url'      => ['text'],
        ]);
    }

    protected static function clientMarkupsTable(): bool
    {
        return self::createTable(Edition::table('ClientMarkups'), function($table) {
            $table->increments('id');
            $table->integer('client_id')->default(0)->index();
            $table->integer('product_id')->default(0)->index();
            $table->decimal('markup', 8, 2);
            $table->dateTime('created_at');
            $table->unique(['client_id', 'product_id']);
        });
    }

    protected static function catalogueChangesTable(): bool
    {
        return self::createTable(Edition::table('CatalogueChanges'), function($table) {
            $table->increments('id');
            $table->string('vendor', 32);
            $table->string('remote_id', 191);
            $table->string('product_name', 255)->default('');
            $table->integer('whmcs_pid')->default(0);
            $table->string('type', 32);
            $table->string('field', 64)->default('');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->text('action')->nullable();
            $table->dateTime('created_at')->index();
            $table->dateTime('emailed_at')->nullable()->index();
        });
    }

    protected static function autoInstallReportTable(): bool
    {
        return self::createTable(Edition::table('AutoInstallReport'), function($table) {
            $table->increments('id');
            $table->integer('service_id')->index();
            $table->integer('client_id')->default(0);
            $table->string('domain')->default('');
            $table->string('kind', 16);
            $table->string('status', 16)->default('pending')->index();
            $table->text('detail')->nullable();
            $table->dateTime('started_at');
            $table->dateTime('finished_at')->nullable();
            $table->dateTime('late_at')->nullable();
            $table->dateTime('emailed_at')->nullable();
        });
    }

    protected static function autoInstallQueueTables(): bool
    {
        $queue = self::createTable(Edition::table('AutoInstallQueue'), function($table) {
            $table->increments('id');
            $table->integer('client_id');
            $table->integer('service_id')->index();
            $table->string('type', 32);
            $table->string('status', 32)->default('pending');
            $table->string('domain')->nullable();
            $table->integer('attempts')->default(0);
            $table->text('data')->nullable();
            $table->string('error_message', 512)->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
        $logs = self::createTable(Edition::table('AutoInstallLogs'), function($table) {
            $table->increments('id');
            $table->integer('client_id');
            $table->integer('service_id')->index();
            $table->string('type', 16);
            $table->text('message');
            $table->dateTime('created_at');
        });

        return $queue || $logs;
    }

    /**
     * Housekeeping after the steps of an upgrade ran (not a numbered step: its lists grow with releases): the e-mail
     * templates of e-mails the module no longer has - only this edition's own templates, never a legacy module's.
     */
    protected static function afterUpgrade(array &$done)
    {
        if(!class_exists('\\ModulesGarden\\TSSGGSModule\\App\\Helpers\\EmailTemplates'))
        {
            return;
        }
        foreach(\ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates::REMOVED_TEMPLATES as $key)
        {
            if(Capsule::table('tblemailtemplates')->where('name', Edition::templateName($key))->delete())
            {
                $done[] = 'removed e-mail template ' . Edition::templateName($key);
            }
        }
    }

    // ---- helpers ---- //

    protected static function hasTable(string $table): bool
    {
        return Capsule::schema()->hasTable($table);
    }

    protected static function hasIndexOn(string $table, string $column): bool
    {
        return (bool)Capsule::selectOne('SELECT 1 AS x FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? AND seq_in_index = 1', [$table, $column]);
    }

    /** creates the table when it is missing (InnoDB) */
    protected static function createTable(string $table, callable $columns): bool
    {
        if(self::hasTable($table))
        {
            return false;
        }
        Capsule::schema()->create($table, function($blueprint) use ($columns) {
            $blueprint->engine = 'InnoDB';
            $columns($blueprint);
        });

        return true;
    }

    /**
     * adds the missing columns, all nullable; [name => ['string', length] | ['text']]. A table that is not there is
     * left alone (schema.sql creates it with the columns).
     */
    protected static function addColumns(string $table, array $columns): bool
    {
        $schema = Capsule::schema();
        if(!$schema->hasTable($table))
        {
            return false;
        }
        $missing = array_filter(array_keys($columns), function($column) use ($schema, $table) {
            return !$schema->hasColumn($table, $column);
        });
        if(!$missing)
        {
            return false;
        }
        $schema->table($table, function($blueprint) use ($columns, $missing) {
            foreach($missing as $column)
            {
                $type = $columns[$column];
                $type[0] === 'text' ? $blueprint->text($column)->nullable() : $blueprint->string($column, $type[1] ?? 255)->nullable();
            }
        });

        return true;
    }

    protected static function quote(string $table): string
    {
        return '`' . str_replace('`', '', $table) . '`';
    }
}
