<?php

namespace ModulesGarden\TSSGGSModule\App\Migration\Support;

use WHMCS\Database\Capsule;

/**
 * Small database helpers used across the migration. Plain Capsule, no framework classes.
 */
class Schema
{
    protected static $tables  = null;
    protected static $columns = [];

    public static function tableExists(string $table): bool
    {
        if(self::$tables === null)
        {
            self::$tables = [];
            foreach(Capsule::select('SHOW TABLES') as $row)
            {
                $row            = (array)$row;
                $name           = (string)reset($row);
                self::$tables[strtolower($name)] = $name;
            }
        }

        return isset(self::$tables[strtolower($table)]);
    }

    public static function columnExists(string $table, string $column): bool
    {
        if(!self::tableExists($table))
        {
            return false;
        }

        if(!isset(self::$columns[$table]))
        {
            self::$columns[$table] = [];
            foreach(Capsule::select('SHOW COLUMNS FROM `' . str_replace('`', '', $table) . '`') as $row)
            {
                $row = (array)$row;
                self::$columns[$table][strtolower($row['Field'])] = true;
            }
        }

        return isset(self::$columns[$table][strtolower($column)]);
    }

    public static function count(string $table, array $where = []): int
    {
        if(!self::tableExists($table))
        {
            return 0;
        }

        $query = Capsule::table($table);
        foreach($where as $column => $value)
        {
            is_array($value) ? $query->whereIn($column, $value) : $query->where($column, $value);
        }

        return (int)$query->count();
    }

    public static function quoteIdentifier(string $name): string
    {
        return '`' . str_replace('`', '', $name) . '`';
    }

    /** Is there an index whose first column is $column? */
    public static function hasIndexOn(string $table, string $column): bool
    {
        return (bool)Capsule::selectOne('SELECT 1 AS x FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? AND seq_in_index = 1 LIMIT 1', [$table, $column]);
    }

    public static function hasIndex(string $table, string $name): bool
    {
        return (bool)Capsule::selectOne('SELECT 1 AS x FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1', [$table, $name]);
    }

    /**
     * Adds an index on $column when the table has none starting with it. Returns true when one was added.
     * DDL: never call it inside a transaction (MySQL would commit it).
     */
    public static function addIndex(string $table, string $column, string $name): bool
    {
        if(!self::columnExists($table, $column) || self::hasIndexOn($table, $column))
        {
            return false;
        }
        Capsule::statement('ALTER TABLE ' . self::quoteIdentifier($table) . ' ADD INDEX ' . self::quoteIdentifier($name) . ' (' . self::quoteIdentifier($column) . ')');

        return true;
    }

    public static function dropIndex(string $table, string $name): bool
    {
        if(!self::tableExists($table) || !self::hasIndex($table, $name))
        {
            return false;
        }
        Capsule::statement('ALTER TABLE ' . self::quoteIdentifier($table) . ' DROP INDEX ' . self::quoteIdentifier($name));

        return true;
    }

    /**
     * Forget cached table/column lists (after CREATE/DROP TABLE)
     */
    public static function refresh(): void
    {
        self::$tables  = null;
        self::$columns = [];
    }
}
