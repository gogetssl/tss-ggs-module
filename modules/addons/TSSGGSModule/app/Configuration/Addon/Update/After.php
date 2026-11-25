<?php

namespace ModulesGarden\TSSGGSModule\App\Configuration\Addon\Update;

use ModulesGarden\TSSGGSModule\Core\Database\FileLoader;
use ModulesGarden\TSSGGSModule\Core\ModuleConstants;

/**
 * runs after module update actions
 */
class After extends \ModulesGarden\TSSGGSModule\Core\Configuration\Addon\Update\After
{
    /**
     * @param array $params
     * @return array
     */
    public function execute(array $params = [])
    {
        $return = parent::execute($params);

        $fileLoader = new FileLoader();
        $fileLoader->performQueryFromFile(ModuleConstants::getFullPath('app', 'Database', 'schema.sql'));
        // the later changes: columns, indexes, tables added since (App\Database\Schema)
        \ModulesGarden\TSSGGSModule\App\Database\Schema::migrate();

        return $return;
    }
}
