<?php

namespace ModulesGarden\TSSGGSModule\Core\Configuration\Addon\Deactivate;

use ModulesGarden\TSSGGSModule\Core\Configuration\Addon\AbstractAfter;

/**
 * Runs after addon deactivation
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
