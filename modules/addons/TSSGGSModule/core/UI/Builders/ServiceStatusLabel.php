<?php

namespace ModulesGarden\TSSGGSModule\Core\UI\Builders;

use ModulesGarden\TSSGGSModule\Components\Label\Label;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Translator;
use ModulesGarden\TSSGGSModule\Core\UI\Enums\ServiceStatusConfig;

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