<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\Services;

use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Product;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\ProductConfigOption;
use ModulesGarden\TTSGGSModule\Packages\Product\Helpers\ConfigurableOptionHelper;
use ModulesGarden\TTSGGSModule\Packages\Product\Libs\ConfigurableOptions\AbstractConfigurableOption;

class ConfigurableOptions
{
    protected $configOptions = null;
    protected Product $product;
    protected ConfigurableOptionsGroup $configurableOptionsGroupService;

    public function __construct(Product $product)
    {
        $this->product                         = $product;
        $this->configurableOptionsGroupService = new ConfigurableOptionsGroup();
    }

    public function createConfigurableOption(AbstractConfigurableOption $configurableOption)
    {
        if ($this->hasConfigurableOption($configurableOption->getName()))
        {
            return false;
        }

        $group = $this->configurableOptionsGroupService->getFirstOrCreateRelated($this->product);
        $configurableOption->setGroupId($group->id);
        return $configurableOption->create($this->product);
    }

    protected function hasConfigurableOption($optionName = null): bool
    {
        if (!is_string($optionName) || trim($optionName) === '')
        {
            return false;
        }

        $rawOptionName = $this->parseConfigOptionName($optionName);
        $this->loadConfigOptions();

        foreach ($this->configOptions as $option)
        {
            if ($rawOptionName === $this->parseConfigOptionName($option->optionname))
            {
                return true;
            }
        }

        return false;
    }

    protected function parseConfigOptionName($optionName): string
    {
        return explode('|', (string)$optionName)[0];
    }

    protected function loadConfigOptions($force = false)
    {
        if ($force || $this->configOptions === null)
        {
            $this->configOptions = ProductConfigOption::ofProductId($this->product->id)->get();
        }
    }

    public function getConfigurableOptionByName(string $optionName)
    {
        if (trim($optionName) === '')
        {
            return null;
        }

        $rawOptionName = ConfigurableOptionHelper::parseConfigOptionName($optionName);
        $this->loadConfigOptions();

        foreach ($this->configOptions as $option)
        {
            if ($rawOptionName === ConfigurableOptionHelper::parseConfigOptionName($option->optionname))
            {
                return $option;
            }
        }

        return null;
    }
}