<?php

namespace ModulesGarden\TTSGGSModule\App\Configuration\Addon\Activate;

/**
 * Runs after module activation actions
 */
class After extends \ModulesGarden\TTSGGSModule\Core\Configuration\Addon\Activate\After
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
