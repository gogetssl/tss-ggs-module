<?php

namespace ModulesGarden\TTSGGSModule\App\Configuration\Addon\Deactivate;

/**
 * Runs before addon deactivation
 */
class Before extends \ModulesGarden\TTSGGSModule\Core\Configuration\Addon\Deactivate\Before
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
