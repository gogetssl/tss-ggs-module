<?php

namespace ModulesGarden\TTSGGSModule\Core\Configuration\Addon\Config;

use ModulesGarden\TTSGGSModule\Core\Configuration\Addon\AbstractAfter;

/**
 * Runs after loading module configuration
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
