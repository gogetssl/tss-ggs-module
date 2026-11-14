<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use WHMCS\Database\Capsule;

/**
 * Up to 1.2.0 every table of the module was named after the folder it was installed in (tssmodule_, ggsmodule_).
 * From 1.3.0 the edition names them (TSSPlugin_, GGSModule_), so an install made earlier has its tables renamed
 * once, on activation and on upgrade. The client markups table is renamed with the feature it belongs to.
 *
 * Nothing is dropped: a table whose new name is already taken is left where it is and reported.
 */
class TablePrefix
{
    /**
     * Prefix used before 1.3.0: the folder this module sits in, and nothing else. A WHMCS that has both editions
     * installed keeps two sets of tables, and each edition must only ever rename its own.
     */
    protected static function oldPrefixes(): array
    {
        return [basename(dirname(__DIR__, 2))];
    }

    /** Tables that changed their name with the Commissions -> Markups rename */
    const RENAMED = ['UserCommissions' => 'ClientMarkups'];

    /** Columns that changed with them: table (new name) => [old column => new column] */
    const RENAMED_COLUMNS = ['ClientMarkups' => ['commission' => 'markup DECIMAL(8,2) NOT NULL']];

    /**
     * Renames what is left of an older install. Safe to call again: it only acts on tables that are still there.
     *
     * @return array the renames that were made, as "old -> new"
     */
    public static function migrate(): array
    {
        $new  = Edition::tablePrefix();
        $done = [];

        foreach(self::oldPrefixes() as $old)
        {
            if($old === $new)
            {
                continue;
            }

            foreach(self::tables($old . '_') as $table)
            {
                $rest   = substr($table, strlen($old) + 1);
                $target = $new . '_' . (self::RENAMED[$rest] ?? $rest);

                if(!self::name($table) || !self::name($target) || self::exists($target))
                {
                    continue;
                }

                try
                {
                    Capsule::statement('RENAME TABLE `' . $table . '` TO `' . $target . '`');
                    $done[] = $table . ' -> ' . $target;
                    self::columns($target, $new);
                }
                catch(\Throwable $e)
                {
                    // a table that cannot be renamed (locked, no permission) stays where it is - the next run tries again
                }
            }
        }

        return $done;
    }

    /**
     * The column renames that go with a table, once it carries its new name
     */
    protected static function columns($table, $prefix): void
    {
        $short = substr($table, strlen($prefix) + 1);
        foreach(self::RENAMED_COLUMNS[$short] ?? [] as $column => $definition)
        {
            try
            {
                if(Capsule::schema()->hasColumn($table, $column))
                {
                    Capsule::statement('ALTER TABLE `' . $table . '` CHANGE `' . $column . '` ' . $definition);
                }
            }
            catch(\Throwable $e)
            {
                // the page reads the new column name only, so a failed change shows as an empty value, not as a crash
            }
        }
    }

    /**
     * Tables of this database whose name starts with the prefix
     */
    protected static function tables($prefix): array
    {
        $names = [];
        try
        {
            $rows = Capsule::select(
                'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE ?',
                [str_replace('_', '\\_', $prefix) . '%']
            );
            foreach($rows as $row)
            {
                $names[] = (string)current((array)$row);
            }
        }
        catch(\Throwable $e)
        {
            return [];
        }

        return $names;
    }

    protected static function exists($table): bool
    {
        try
        {
            return Capsule::schema()->hasTable($table);
        }
        catch(\Throwable $e)
        {
            return true; // unknown: leave the table alone
        }
    }

    /** A table name that goes into a statement is built from our own prefixes, and is checked all the same */
    protected static function name($table): bool
    {
        return (bool)preg_match('/^[A-Za-z0-9_]+$/', (string)$table);
    }
}
