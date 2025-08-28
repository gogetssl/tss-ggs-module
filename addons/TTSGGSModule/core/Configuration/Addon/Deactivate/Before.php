<?php

namespace ModulesGarden\TTSGGSModule\Core\Configuration\Addon\Deactivate;

use ModulesGarden\TTSGGSModule\Core\Configuration\Addon\AbstractBefore;

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
