<?php

namespace ModulesGarden\TSSGGSModule\Packages\Product\Support\Factories;

use ModulesGarden\TSSGGSModule\Core\Data\Container;
use ModulesGarden\TSSGGSModule\Core\WHMCS\ModuleParamsBuilder;
use ModulesGarden\TSSGGSModule\Packages\Product\Libs\Configuration\ConfigurationContainer;
use ModulesGarden\TSSGGSModule\Packages\Product\Services\ProductConfiguration;

class ConfigurationFactory
{
    public static function fromProduct(int $productId):ConfigurationContainer
    {
        $productConfiguration = new ProductConfiguration($productId);

        return (new ConfigurationContainer())
            ->setProductSetting($productConfiguration->get());
    }

    public static function fromService(int $serviceId): ConfigurationContainer
    {
        return self::fromParams((new ModuleParamsBuilder())->get($serviceId));
    }

    public static function fromParams(array $params): ConfigurationContainer
    {
        $paramsContainer = new Container($params);

        $configurationContainer = new ConfigurationContainer();

        if ($serviceId = $paramsContainer->get('serviceid'))
        {
            $configurationContainer->setServiceId($serviceId);
        }

        if ($productId = $paramsContainer->get('packageid'))
        {
            $configurationContainer->setProductSetting((new ProductConfiguration($productId))->get());
        }

        return $configurationContainer
            ->setCustomFields($paramsContainer->get('customfields', []))
            ->setConfigurableOptions($paramsContainer->get('configoptions', []));
    }
}