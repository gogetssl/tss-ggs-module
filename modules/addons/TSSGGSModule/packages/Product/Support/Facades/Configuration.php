<?php

namespace ModulesGarden\TSSGGSModule\Packages\Product\Support\Facades;

use ModulesGarden\TSSGGSModule\Core\Support\Facades\AbstractFacade;
use ModulesGarden\TSSGGSModule\Packages\Product\Libs\Configuration\ConfigurationContainer;

/**
 * @method static ConfigurationContainer getConfiguration()
 * @method static setConfiguration(ConfigurationContainer $configuration)
 */
class Configuration extends AbstractFacade
{
    protected static function getFacadeAccessor(): string
    {
        return \ModulesGarden\TSSGGSModule\Packages\Product\Services\Configuration::class;
    }
}