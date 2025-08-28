<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\Helpers;

class ConfigurableOptionHelper
{
    public static function parseConfigOptionName($optionName):string
    {
        return explode('|', (string)$optionName)[0];
    }
}