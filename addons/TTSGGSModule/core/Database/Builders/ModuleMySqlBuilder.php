<?php

namespace ModulesGarden\TTSGGSModule\Core\Database\Builders;

use ModulesGarden\TTSGGSModule\Core\ModuleConstants;

class ModuleMySqlBuilder extends \Illuminate\Database\Schema\MySqlBuilder
{
    public function getAllTables()
    {
        return array_filter(parent::getAllTables(),function ($table) {
            return str_contains($table->Tables_in_db, ModuleConstants::getPrefixDataBase());
        });
    }
}