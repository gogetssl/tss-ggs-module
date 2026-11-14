<?php

namespace ModulesGarden\TSSGGSModule\App\Migration;

use ModulesGarden\TSSGGSModule\App\Libs\Edition;

use ModulesGarden\TSSGGSModule\App\Migration\Support\Schema;
use WHMCS\Database\Capsule;

/**
 * Baseline snapshot of a TEST install, so the installation + migration can be rehearsed repeatedly.
 * snapshot(): copies legacy, unified and the touched WHMCS tables to <prefix>_base__<table>.
 * reset():    puts every live table back to the baseline, drops migration backups and stray unified tables.
 * Refuses to run unless the WHMCS SystemURL is a known development host. CLI only, no admin UI.
 */
class TestEnvironment
{
    public static function prefix()
    {
        return Edition::table('base__');
    }

    /** hosts (substring of the SystemURL) where this tool may run */
    const ALLOWED_HOSTS = ['ggssl.com', 'localhost', '.test', '.local'];

    /** WHMCS tables the migration writes to (full copies; test installs are small) */
    const WHMCS_TABLES = [
        'tblproducts', 'tblproductconfiggroups', 'tblproductconfigoptions', 'tblproductconfigoptionssub', 'tblproductconfiglinks',
        'tblsslorders', 'tblhosting', 'tbladdonmodules', 'tblconfiguration', 'tblemailtemplates',
    ];

    public static function guard(): void
    {
        $url = strtolower((string)Capsule::table('tblconfiguration')->where('setting', 'SystemURL')->value('value'));
        foreach(self::ALLOWED_HOSTS as $host)
        {
            if($url !== '' && strpos($url, $host) !== false)
            {
                return;
            }
        }
        throw new \RuntimeException('Refusing to run: SystemURL "' . $url . '" is not a development host.');
    }

    /** Live tables that belong to the baseline */
    public static function liveTables(): array
    {
        $tables = self::WHMCS_TABLES;
        foreach(Detector::readers() as $reader)
        {
            $tables = array_merge($tables, $reader->tables());
        }
        foreach(self::unifiedTables() as $table)
        {
            $tables[] = $table;
        }

        return array_values(array_unique(array_filter($tables, [Schema::class, 'tableExists'])));
    }

    /** Unified module tables, without baseline and migration-backup copies */
    public static function unifiedTables(): array
    {
        $rows = Capsule::select('SELECT table_name AS t FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name LIKE ?', ['TSSGGSModule\_%']);
        $out  = [];
        foreach($rows as $r)
        {
            if(strpos($r->t, self::prefix()) !== 0 && strpos($r->t, Backup::prefixBase() . '_') !== 0)
            {
                $out[] = $r->t;
            }
        }

        return $out;
    }

    public static function baselineTables(): array
    {
        $rows = Capsule::select('SELECT table_name AS t, table_rows AS r, (data_length + index_length) AS b FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name LIKE ? ORDER BY table_name', [str_replace('_', '\_', self::prefix()) . '%']);
        $out  = [];
        foreach($rows as $r)
        {
            $out[substr($r->t, strlen(self::prefix()))] = ['table' => $r->t, 'rows' => (int)$r->r, 'bytes' => (int)$r->b];
        }

        return $out;
    }

    /** Takes (or with $refresh retakes) the baseline. Returns [table => rows]. */
    public static function snapshot(bool $refresh = false): array
    {
        self::guard();
        if(self::baselineTables() && !$refresh)
        {
            throw new \RuntimeException('A baseline already exists. Use --refresh to replace it.');
        }
        self::dropBaseline();

        $done = [];
        foreach(self::liveTables() as $table)
        {
            $target = self::prefix() . $table;
            Capsule::statement('CREATE TABLE ' . Schema::quoteIdentifier($target) . ' LIKE ' . Schema::quoteIdentifier($table));
            Capsule::statement('INSERT INTO ' . Schema::quoteIdentifier($target) . ' SELECT * FROM ' . Schema::quoteIdentifier($table));
            $done[$table] = (int)Capsule::table($target)->count();
        }
        Schema::refresh();

        return $done;
    }

    /**
     * Restores every baseline table, drops migration backups and unified tables that did not exist at snapshot time.
     * $fresh also removes the unified module's configuration rows so the wizard starts at step 1.
     * Returns a summary.
     */
    public static function reset(bool $fresh = false): array
    {
        self::guard();
        $baseline = self::baselineTables();
        if(!$baseline)
        {
            throw new \RuntimeException('No baseline found. Run snapshot first.');
        }

        $restored = [];
        Capsule::statement('SET FOREIGN_KEY_CHECKS = 0');
        try
        {
            foreach($baseline as $table => $info)
            {
                if(!Schema::tableExists($table))
                {
                    Capsule::statement('CREATE TABLE ' . Schema::quoteIdentifier($table) . ' LIKE ' . Schema::quoteIdentifier($info['table']));
                }
                Capsule::statement('DELETE FROM ' . Schema::quoteIdentifier($table));
                Capsule::statement('INSERT INTO ' . Schema::quoteIdentifier($table) . ' SELECT * FROM ' . Schema::quoteIdentifier($info['table']));
                $restored[$table] = (int)Capsule::table($table)->count();
            }

            // unified tables created after the snapshot (e.g. by a later module version) and migration backups
            $dropped = [];
            foreach(self::unifiedTables() as $table)
            {
                if(!isset($baseline[$table]))
                {
                    Capsule::statement('DROP TABLE ' . Schema::quoteIdentifier($table));
                    $dropped[] = $table;
                }
            }
            foreach(Capsule::select('SELECT table_name AS t FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name LIKE ?', ['TSSGGSModule\_bak\_%']) as $r)
            {
                Capsule::statement('DROP TABLE ' . Schema::quoteIdentifier($r->t));
                $dropped[] = $r->t;
            }

            if($fresh)
            {
                Capsule::table('tbladdonmodules')->where('module', 'configuration_TSSGGSModule')->delete();
            }
        }
        finally
        {
            Capsule::statement('SET FOREIGN_KEY_CHECKS = 1');
            Schema::refresh();
        }

        return ['restored' => $restored, 'dropped' => $dropped, 'fresh' => $fresh];
    }

    public static function dropBaseline(): int
    {
        $n = 0;
        foreach(self::baselineTables() as $info)
        {
            Capsule::statement('DROP TABLE ' . Schema::quoteIdentifier($info['table']));
            $n++;
        }
        Schema::refresh();

        return $n;
    }
}
