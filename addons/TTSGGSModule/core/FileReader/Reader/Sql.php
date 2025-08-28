<?php

namespace ModulesGarden\TTSGGSModule\Core\FileReader\Reader;

use Exception;
use Illuminate\Database\Capsule\Manager;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\ServiceLocator;

/**
 * Description of Sql
 */
class Sql extends AbstractType
{
    protected function loadFile()
    {
        $return = '';
        if (file_exists($this->path . DIRECTORY_SEPARATOR . $this->file))
        {
            $collation = $this->getWHMCSTablesCollation();
            $charset   = $this->getWHMCSTablesCharset();
            $return    = file_get_contents($this->path . DIRECTORY_SEPARATOR . $this->file);
            $return    = str_replace('#collation#', $collation, $return);
            $return    = str_replace('#charset#', $charset, $return);
            $return    = str_replace('#prefix#', ModuleConstants::getPrefixDataBase(), $return);
            foreach ($this->renderData as $key => $value)
            {
                $return = str_replace("#$key#", $value, $return);
            }
        }

        $this->data = $return;
    }

    protected function getWHMCSTablesCollation()
    {
        $pdo   = Manager::connection()->getPdo();
        $query = $pdo->prepare("SHOW TABLE STATUS WHERE name = 'tblclients'");
        $query->execute();
        $result = $query->fetchObject();

        return $result->Collation;
    }

    protected function getWHMCSTablesCharset()
    {
        require ROOTDIR . DIRECTORY_SEPARATOR . 'configuration.php';

        $pdo = Manager::connection()->getPdo();

        $query = $pdo->prepare("SELECT CCSA.character_set_name as Charset FROM information_schema.`TABLES` T,
            information_schema.`COLLATION_CHARACTER_SET_APPLICABILITY` CCSA
            WHERE CCSA.collation_name = T.table_collation
            AND T.table_schema = :db_name
            AND T.table_name = 'tblclients';");

        $query->execute(['db_name' => $db_name]);
        $result = $query->fetchObject();

        return $result->Charset;
    }
}
