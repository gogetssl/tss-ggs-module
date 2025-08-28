<?php

namespace ModulesGarden\TTSGGSModule\Core\Traits;

use ModulesGarden\TTSGGSModule\Core\ServiceLocator;

/**
 * Description of IsDebugOn
 *
 * @deprecated
 */
trait IsDebugOn
{
    public function isDebugOn()
    {
        return \ModulesGarden\TTSGGSModule\Core\Support\Facades\Config::get('configuration.debug');
    }
}
