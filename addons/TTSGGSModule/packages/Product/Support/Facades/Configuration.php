<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\Support\Facades;

use ModulesGarden\TTSGGSModule\Core\Support\Facades\AbstractFacade;
use ModulesGarden\TTSGGSModule\Packages\Product\Libs\Configuration\ConfigurationContainer;

/**
 * @method static ConfigurationContainer getConfiguration()
 * @method static setConfiguration(ConfigurationContainer $configuration)
 */
class Configuration extends AbstractFacade
{
    protected static function getFacadeAccessor(): string
    {
        return \ModulesGarden\TTSGGSModule\Packages\Product\Services\Configuration::class;
    }
}