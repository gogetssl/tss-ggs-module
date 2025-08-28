<?php

namespace ModulesGarden\TTSGGSModule\Core\Configuration\Addon\Deactivate;

use ModulesGarden\TTSGGSModule\Core\Configuration\Addon\AbstractAfter;

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
