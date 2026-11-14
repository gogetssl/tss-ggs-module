<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use WHMCS\Database\Capsule;

/**
 * Indexes added after a table was first created (schema.sql only creates missing tables). Runs with the other
 * install steps (activate, upgrade, configuration, cron) and does nothing once they are there.
 *
 * Requests.serviceid: every page of a service and every sync reads its rows by service; without the index each of
 * those reads the whole table, which a large install (or a migration of thousands of services) feels.
 */
class SchemaIndexes
{
    /** [table name without prefix, column, index name] */
    const INDEXES = [
        ['Requests', 'serviceid', 'serviceid'],
    ];

    public static function migrate(): array
    {
        $added = [];
        foreach(self::INDEXES as [$name, $column, $index])
        {
            $table = Edition::table($name);
            $there = Capsule::selectOne('SELECT 1 AS x FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?', [$table, $column]);
            if(!$there)
            {
                continue; // not installed yet: schema.sql creates it with the index
            }
            $indexed = Capsule::selectOne('SELECT 1 AS x FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? AND seq_in_index = 1', [$table, $column]);
            if(!$indexed)
            {
                Capsule::statement('ALTER TABLE `' . str_replace('`', '', $table) . '` ADD INDEX `' . $index . '` (`' . $column . '`)');
                $added[] = $table . '.' . $column;
            }
        }

        return $added;
    }
}
