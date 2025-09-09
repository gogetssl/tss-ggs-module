<?php

namespace ModulesGarden\TSSGGSModule\Core\Configuration\Addon\Update;

use ModulesGarden\TSSGGSModule\Core\Configuration\Addon\AbstractAfter;

/**
 * runs after module update actions
 */
class After extends AbstractAfter
{
    /**
     * @param array $params
     * @return array
     */
    public function execute(array $params = [])
    {
        return $params;
    }
}
