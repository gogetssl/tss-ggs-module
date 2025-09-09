<?php

namespace ModulesGarden\TSSGGSModule\App\Configuration\Addon\Update;

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

        return $return;
    }
}
