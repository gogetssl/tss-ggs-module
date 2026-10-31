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

    /**
     * Forget cached table/column lists (after CREATE/DROP TABLE)
     */
    public static function refresh(): void
    {
        self::$tables  = null;
        self::$columns = [];
    }
}
