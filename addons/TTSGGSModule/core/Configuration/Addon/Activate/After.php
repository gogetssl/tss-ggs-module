<?php

namespace ModulesGarden\TTSGGSModule\Core\Configuration\Addon\Activate;

use ModulesGarden\TTSGGSModule\Core\Configuration\Addon\AbstractAfter;

/**
 * Runs after module activation actions
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
