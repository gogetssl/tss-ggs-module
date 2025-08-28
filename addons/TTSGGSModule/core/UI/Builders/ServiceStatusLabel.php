<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Builders;

use ModulesGarden\TTSGGSModule\Components\Label\Label;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Translator;
use ModulesGarden\TTSGGSModule\Core\UI\Enums\ServiceStatusConfig;

class ServiceStatusLabel
{
    public static function create(string $type): Label
    {
        $config = ServiceStatusConfig::byStatus($type);

        $label = new Label();
        $label->setText(Translator::get("service.status.{$type}"));
        $label->displayAsStatusLabel();
        $label->setType($config['type']);

        return $label;
    }

}