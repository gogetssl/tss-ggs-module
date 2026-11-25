<?php

namespace ModulesGarden\TSSGGSModule\App\Database;

use ModulesGarden\TSSGGSModule\App\Libs\Support\Edition;
use WHMCS\Database\Capsule;

/**
 * Runs the statements of an SQL file (app/Database/schema.sql). In the file "#prefix#" is the prefix of the module's
 * tables ("GGSModule_"), "#charset#" and "#collation#" are those of the WHMCS tables (taken from tblclients), so the
 * module's tables can be joined with them. Statements end with ";" (no ";" inside a statement or a comment).
 */
class SqlFile
{
    public static function run(string $file): void
    {
        if(!is_file($file))
        {
            return;
        }
        $pdo = Capsule::connection()->getPdo();
        $sql = strtr((string)file_get_contents($file), [
            '#prefix#'    => Edition::tablePrefix() . '_',
            '#charset#'   => self::charset($pdo),
            '#collation#' => self::collation($pdo),
        ]);
        foreach(explode(';', $sql) as $statement)
        {
            if(trim($statement) !== '')
            {
                $pdo->prepare($statement)->execute();
            }
        }
    }

    protected static function collation(\PDO $pdo): string
    {
        $row = $pdo->query("SHOW TABLE STATUS WHERE name = 'tblclients'")->fetchObject();

        return (string)($row->Collation ?? 'utf8mb4_unicode_ci');
    }

    protected static function charset(\PDO $pdo): string
    {
        $query = $pdo->prepare('SELECT CCSA.character_set_name AS Charset FROM information_schema.`TABLES` T, information_schema.`COLLATION_CHARACTER_SET_APPLICABILITY` CCSA'
            . " WHERE CCSA.collation_name = T.table_collation AND T.table_schema = DATABASE() AND T.table_name = 'tblclients'");
        $query->execute();
        $row = $query->fetchObject();

        return (string)($row->Charset ?? 'utf8mb4');
    }
}
