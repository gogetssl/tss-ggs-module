<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\UI\Formatters;

use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Components\Span\Span;
use ModulesGarden\TTSGGSModule\Components\Text\Text;
use ModulesGarden\TTSGGSModule\Components\Text\TextBold;

class ConfigOptionFullNameFormatter
{
    protected const CONFIG_OPTION_NAMES_SEPARATOR = "|";

    public static function buildFullNameContainer(string $optionFullName): Container
    {
        $elements = explode(self::CONFIG_OPTION_NAMES_SEPARATOR, $optionFullName);
        $configOptionSysName = $elements[0];
        $configOptionFriendlyName= $elements[1];

        $span = new Span();

        $span->addElement((new TextBold())->setText($configOptionSysName));

        if (!empty($configOptionFriendlyName))
        {
            $span->addElement((new Text())->setText(self::CONFIG_OPTION_NAMES_SEPARATOR . $elements[1]));
        }

        return $span;
    }
}