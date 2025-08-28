<?php

namespace ModulesGarden\TTSGGSModule\Core\Configuration\Addon\Activate;

use ModulesGarden\TTSGGSModule\Core\Configuration\Addon\AbstractBefore;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\ServiceLocator;

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
