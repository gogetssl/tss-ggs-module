<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\Helpers;

use ModulesGarden\TTSGGSModule\Core\Support\Facades\Config;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Product;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Addon;
use ModulesGarden\TTSGGSModule\Packages\Product\Enums\ConfigSettings;
use ModulesGarden\TTSGGSModule\Packages\Product\Libs\CustomFields\AbstractCustomField;
use ModulesGarden\TTSGGSModule\Packages\Product\Services\CustomFields;

class RequiredCustomFieldsGenerator
{
    public static function addRequiredProductCustomFields(int $productId)
    {
        $product = Product::findOrFail($productId);

        self::createCustomFields(new CustomFields($product->id, CustomFields::TYPE_PRODUCT), ConfigSettings::PRODUCT_CUSTOM_FIELDS);
    }

    public static function addRequiredAddonCustomFields(int $addonId)
    {
        $addon = Addon::findOrFail($addonId);

        self::createCustomFields(new CustomFields($addon->id, CustomFields::TYPE_ADDON), ConfigSettings::ADDON_CUSTOM_FIELDS);
    }

    private static function createCustomFields(CustomFields $service, string $configSetting)
    {
        foreach (Config::get($configSetting) as $customField)
        {
            if ($customField instanceof AbstractCustomField)
            {
                $service->createCustomField($customField);
            }
        }
    }
}