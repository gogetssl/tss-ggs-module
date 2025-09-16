<?php

namespace ModulesGarden\TSSGGSModule\Core\Configuration\Addon\Deactivate;

use ModulesGarden\TSSGGSModule\Core\Configuration\Addon\AbstractBefore;

/**
 * Runs before addon deactivation
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
