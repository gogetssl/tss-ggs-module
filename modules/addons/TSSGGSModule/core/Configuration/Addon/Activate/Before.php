<?php

namespace ModulesGarden\TSSGGSModule\Core\Configuration\Addon\Activate;

use ModulesGarden\TSSGGSModule\Core\Configuration\Addon\AbstractBefore;
use ModulesGarden\TSSGGSModule\Core\ModuleConstants;
use ModulesGarden\TSSGGSModule\Core\ServiceLocator;

/**
 * Runs before module activation actions
 */
class Before extends AbstractBefore
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
