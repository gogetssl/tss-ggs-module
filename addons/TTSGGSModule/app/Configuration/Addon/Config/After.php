<?php

namespace ModulesGarden\TTSGGSModule\App\Configuration\Addon\Config;

/**
 * Runs after loading module configuration
 */
class After extends \ModulesGarden\TTSGGSModule\Core\Configuration\Addon\Config\After
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
