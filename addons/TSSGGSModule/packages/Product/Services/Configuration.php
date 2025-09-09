<?php

namespace ModulesGarden\TSSGGSModule\Packages\Product\Services;

use ModulesGarden\TSSGGSModule\Core\Support\Facades\Params;
use ModulesGarden\TSSGGSModule\Packages\Product\Libs\Configuration\ConfigurationContainer;
use ModulesGarden\TSSGGSModule\Packages\Product\Support\Factories\ConfigurationFactory;

class Configuration
{
    protected static ConfigurationContainer $configurationContainer;

    public function __construct()
    {
        $this->load();
    }

    protected function load():void
    {
        static::$configurationContainer = ConfigurationFactory::fromParams(Params::all());
    }

    public function getConfiguration():ConfigurationContainer
    {
        return static::$configurationContainer;
    }

    public function setConfiguration(ConfigurationContainer $configuration):self
    {
        static::$configurationContainer = $configuration;

        return $this;
    }
}