<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\UI\Providers;

use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TTSGGSModule\Packages\Product\Helpers\ProductConfiguration as ProductConfigurationHelper;
use \ModulesGarden\TTSGGSModule\Packages\Product\Services\ProductConfiguration as ProductConfigurationService;

class ProductConfiguration extends CrudProvider
{
    public function read()
    {
        $type = ProductConfigurationHelper::isRunAsProductAddon() ? ProductConfigurationService::PRODUCT_ADDON_TYPE : ProductConfigurationService::PRODUCT_TYPE;

        $data = (new ProductConfigurationService(Request::get('id', 0), $type))
            ->get();

        foreach ($data as $setting => $value)
        {
            $this->data[sprintf('customconfigoption[%s]', $setting)] = $value;

            if (is_array($value))
            {
                foreach ($value as $key => $subValue)
                {
                    $this->data[sprintf("customconfigoption[%s][{$key}]", $setting)] = $subValue;
                }
            }
        }
    }
}
