<?php

namespace ModulesGarden\TSSGGSModule\App\Configuration\Addon\Activate;

/**
 * Runs after module activation actions
 */
class After extends \ModulesGarden\TSSGGSModule\Core\Configuration\Addon\Activate\After
{
    /**
     * @param array $params
     * @return array
     */
    public function execute(array $params = [])
    {
        $return = parent::execute($params);

        // schema.sql created the tables: the later changes (App\Database\Schema) are recorded as applied now
        \ModulesGarden\TSSGGSModule\App\Database\Schema::migrate();

        return $return;
    }
}
