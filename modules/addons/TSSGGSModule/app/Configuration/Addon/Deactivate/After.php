<?php

namespace ModulesGarden\TSSGGSModule\App\Configuration\Addon\Deactivate;

/**
 * Runs after addon deactivation
 */
class After extends \ModulesGarden\TSSGGSModule\Core\Configuration\Addon\Deactivate\After
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
