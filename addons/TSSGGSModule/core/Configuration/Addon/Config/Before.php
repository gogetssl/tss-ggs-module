<?php

namespace ModulesGarden\TSSGGSModule\Core\Configuration\Addon\Config;

use ModulesGarden\TSSGGSModule\Core\Configuration\Addon\AbstractBefore;

/**
 * Runs before loading module configuration
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
