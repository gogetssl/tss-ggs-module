<?php

namespace ModulesGarden\TSSGGSModule\Core\Traits;

use ModulesGarden\TSSGGSModule\Core\ServiceLocator;

/**
 * Description of IsDebugOn
 *
 * @deprecated
 */
trait IsDebugOn
{
    public function isDebugOn()
    {
        return \ModulesGarden\TSSGGSModule\Core\Support\Facades\Config::get('configuration.debug');
    }
}
