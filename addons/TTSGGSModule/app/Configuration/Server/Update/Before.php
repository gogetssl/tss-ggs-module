<?php

namespace ModulesGarden\TTSGGSModule\App\Configuration\Server\Update;

/**
 * runs before module update actions
 */
class Before extends \ModulesGarden\TTSGGSModule\Core\Configuration\Addon\Update\Before
{
    /**
     * @return array
     */
    public function execute($version)
    {
        $return = parent::execute($version);

        return $return;
    }
}
