<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\Libs\Configuration;

use ModulesGarden\TTSGGSModule\Core\Data\Container;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Config;

class ConfigurationContainer
{
    protected int $serviceId;

    protected Container $customFields;
    protected Container $configurableOptions;
    protected Container $productSetting;

    public function __construct()
    {
        $this->customFields         = new Container();
        $this->configurableOptions  = new Container();
        $this->productSetting       = new Container();
    }

    public function setServiceId(int $serviceId): self
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    public function setCustomFields(array $customFields): self
    {
        $this->customFields = new Container($customFields);

        return $this;
    }

    public function setConfigurableOptions(array $configurableOptions): self
    {
        $this->configurableOptions = new Container($configurableOptions);

        return $this;
    }

    public function setProductSetting(array $productSetting): self
    {
        $this->productSetting = new Container($productSetting);

        return $this;
    }

    public function getCustomField(string $customFieldName, $default = null)
    {
        return $this->customFields->get($customFieldName, $default);
    }

    public function setCustomField(string $name, $value = null):self
    {
        if (!isset($this->serviceId))
        {
            throw new \Exception('No service params loaded. Please load service params first');
        }

        $service = new ServiceCustomFieldsValues($this->serviceId);

        $service->set([$name => $value]);

        return $this;
    }

    public function getConfigurableOption(string $configurableOptionName, $default = null)
    {
        return $this->configurableOptions->get($configurableOptionName, $default);
    }

    public function getProductSetting(string $productSettingName, $default = null)
    {
        return $this->productSetting->get($productSettingName, $default);
    }

    public function get(string $settingName, ?string $fallback = null, $default = null)
    {
        return $this->searchConfig($settingName, $fallback ? $this->searchConfig($fallback, $default) : $default);
    }

    public function getConfig(string $settingName, $default = null)
    {
        if ($this->isInAlternateMode($settingName))
        {
            return $this->getConfigurableOption($settingName, 0) + $this->getProductSetting($settingName, 0);
        }

        return $this->getConfigurableOption($settingName, $this->getProductSetting($settingName, $default));
    }

    public function pluck():array
    {
        $result = [];

        foreach (func_get_args() as $paramName)
        {
            $result[$paramName] = $this->get($paramName);
        }

        return $result;
    }

    protected function searchConfig(string $settingName, $default = null)
    {
        return $this->getCustomField($settingName, $this->getConfigurableOption($settingName, $this->getProductSetting($settingName, $default)));
    }

    protected function isInAlternateMode($configName): bool
    {
        return in_array($configName, $this->getProductSetting(Config::get('product.alternateModeSettingName', 'alternateMode'), []));
    }
}