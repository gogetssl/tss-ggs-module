<?php

namespace ModulesGarden\TSSGGSModule\Packages\Logs\UI\Others;

use ModulesGarden\TSSGGSModule\Packages\Logs\Support\Translations\LogsTypeTranslator;
use ModulesGarden\TSSGGSModule\Components\Label\Label;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Config;

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
