<?php

namespace ModulesGarden\TTSGGSModule\Packages\Logs\UI\Others;

use ModulesGarden\TTSGGSModule\Packages\Logs\Support\Translations\LogsTypeTranslator;
use ModulesGarden\TTSGGSModule\Components\Label\Label;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Config;

//@todo refactor me - colors should be loaded from static class, do not use static in this class
class TypeLabel
{
    public static function create($type)
    {
        $config = Config::get('install.logs.colors.' . $type, []);

        $label = new Label();
        $label->setText(strtoupper((new LogsTypeTranslator())->translateType($type)));
        $label->displayAsStatusLabel();
        $config['type'] ? $label->setType($config['type']) : null;
        $config['textColor'] ? $label->setTextColor($config['textColor']) : null;

        return $label;
    }
}
