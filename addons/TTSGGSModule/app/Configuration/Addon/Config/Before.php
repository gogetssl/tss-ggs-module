<?php

namespace ModulesGarden\TTSGGSModule\App\Configuration\Addon\Config;

/**
 * Runs before loading module configuration
 */
class Before extends \ModulesGarden\TTSGGSModule\Core\Configuration\Addon\Config\Before
{
    /**
     * @param array $params
     * @return array
     */
    public function execute(array $params = [])
    {
        $return = parent::execute($params);

        return $return;
    }
}
