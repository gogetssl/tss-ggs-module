<?php

namespace ModulesGarden\TSSGGSModule\App\Migration;

use ModulesGarden\TSSGGSModule\App\Migration\Support\Schema;
use WHMCS\Database\Capsule;

/**
 * Backup tables for a migration run: full copies of the legacy module tables and row copies of the WHMCS rows
 * the migration changes. Named "<prefix>_<table>" so they can be listed, restored and dropped as a set.
 */
class Backup
{
    const PREFIX_BASE = 'TSSGGSModule_bak';

    protected $migrationId;
    protected $prefix;

    public function __construct(int $migrationId, ?string $prefix = null)
    {
        $this->migrationId = $migrationId;
        $this->prefix      = $prefix ?: self::PREFIX_BASE . '_' . $migrationId . '_' . date('Ymd_His');
    }

    public function prefix(): string
    {
        return $this->prefix;
    }

    public function nameFor(string $table): string
    {
        // MySQL table names are limited to 64 characters
        return substr($this->prefix . '__' . $table, 0, 64);
    }

    /**
     * Full copy of a table. Returns the backup table name, or null when the source does not exist.
     */
    public function copyTable(string $table): ?string
    {
        if(!Schema::tableExists($table))
        {
            return null;
        }

        $target = $this->nameFor($table);
        Capsule::statement('CREATE TABLE ' . Schema::quoteIdentifier($target) . ' LIKE ' . Schema::quoteIdentifier($table));
        Capsule::statement('INSERT INTO ' . Schema::quoteIdentifier($target) . ' SELECT * FROM ' . Schema::quoteIdentifier($table));
        Schema::refresh();

        return $target;
    }

    /**
     * Copy of selected rows (WHMCS tables shared with other modules). $where is a Capsule closure.
     */
    public function copyRows(string $table, callable $where): ?string
    {
        if(!Schema::tableExists($table))
        {
            return null;
        }

        $target = $this->nameFor($table);
        Capsule::statement('CREATE TABLE ' . Schema::quoteIdentifier($target) . ' LIKE ' . Schema::quoteIdentifier($table));

        $query = Capsule::table($table);
        $where($query);
        $sql      = $query->toSql();
        $bindings = $query->getBindings();
        Capsule::statement('INSERT INTO ' . Schema::quoteIdentifier($target) . ' ' . $sql, $bindings);
        Schema::refresh();

        return $target;
    }

    /**
     * Restores rows of a WHMCS table from its backup: deletes the live rows with the same primary key, inserts the copies.
     */
    public function restoreRows(string $table, string $primaryKey = 'id'): int
    {
        $source = $this->nameFor($table);
        if(!Schema::tableExists($source) || !Schema::tableExists($table))
        {
            return 0;
        }

        $t = Schema::quoteIdentifier($table);
        $s = Schema::quoteIdentifier($source);
        $k = Schema::quoteIdentifier($primaryKey);

        Capsule::statement("DELETE FROM $t WHERE $k IN (SELECT $k FROM $s)");
        Capsule::statement("INSERT INTO $t SELECT * FROM $s");

        return (int)Capsule::table($source)->count();
    }

    /**
     * Restores a legacy module table completely (truncate + insert from the copy)
     */
    /** Creates a table that has a backup but no longer exists (empty; restoreTable() fills it) */
    public function createMissing(string $table): bool
    {
        $source = $this->nameFor($table);
        if(!Schema::tableExists($source) || Schema::tableExists($table))
        {
            return false;
        }
        Capsule::statement('CREATE TABLE ' . Schema::quoteIdentifier($table) . ' LIKE ' . Schema::quoteIdentifier($source));
        Schema::refresh();

        return true;
    }

    public function restoreTable(string $table): int
    {
        $source = $this->nameFor($table);
        if(!Schema::tableExists($source))
        {
            return 0;
        }
        if(!Schema::tableExists($table))
        {
            Capsule::statement('CREATE TABLE ' . Schema::quoteIdentifier($table) . ' LIKE ' . Schema::quoteIdentifier($source));
        }

        Capsule::statement('DELETE FROM ' . Schema::quoteIdentifier($table));
        Capsule::statement('INSERT INTO ' . Schema::quoteIdentifier($table) . ' SELECT * FROM ' . Schema::quoteIdentifier($source));
        Schema::refresh();

        return (int)Capsule::table($source)->count();
    }

    /**
     * All backup tables of this run with their row counts and approximate size in bytes
     */
    public function tables(): array
    {
        $rows = Capsule::select('SELECT table_name AS t, table_rows AS r, (data_length + index_length) AS b FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name LIKE ?', [$this->prefix . '%']);
        $list = [];
        foreach($rows as $row)
        {
            $list[] = ['table' => $row->t, 'rows' => (int)$row->r, 'bytes' => (int)$row->b];
        }

        return $list;
    }

    public function drop(): int
    {
        $n = 0;
        foreach($this->tables() as $table)
        {
            Capsule::statement('DROP TABLE IF EXISTS ' . Schema::quoteIdentifier($table['table']));
            $n++;
        }
        Schema::refresh();

        return $n;
    }

    /**
     * Estimated size of the tables that would be backed up (shown in the dry run)
     */
    public static function estimateBytes(array $tables): int
    {
        if(!$tables)
        {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($tables), '?'));
        $row          = Capsule::selectOne('SELECT SUM(data_length + index_length) AS b FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name IN (' . $placeholders . ')', array_values($tables));

        return (int)($row->b ?? 0);
    }
}
