<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\Services;

use ModulesGarden\TTSGGSModule\Core\Support\Facades\Params;
use ModulesGarden\TTSGGSModule\Packages\Product\Libs\Configuration\ConfigurationContainer;
use ModulesGarden\TTSGGSModule\Packages\Product\Support\Factories\ConfigurationFactory;

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