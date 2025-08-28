<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\Helpers;

use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;

class ProductConfiguration
{
    public static function isSupportedInRequest(): bool
    {
        return self::isSupported(Request::get('servertype', ''));
    }

    public static function isSupported(string $type): bool
    {
        return ModuleConstants::getModuleName() === $type;
    }

    public static function isRunAsProductAddon(): bool
    {
        return basename($_SERVER["SCRIPT_NAME"]) == "configaddons.php";
    }
}
