<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\ServiceInformation;

use ModulesGarden\TTSGGSModule\Components\Label\Label;
use ModulesGarden\TTSGGSModule\Core\Components\Enums\Color;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxAutoReloadInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxOnLoadInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ClientAreaInterface;
use ModulesGarden\TTSGGSModule\Core\UI\Interfaces\ClientArea;

class Status extends Label implements AjaxOnLoadInterface, AjaxAutoReloadInterface, AdminAreaInterface, ClientAreaInterface
{
    public function loadData(): void
    {
        $types = [
            Color::DANGER,
            Color::INFO,
            Color::SUCCESS,
            Color::WARNING,
        ];

        $type = $types[rand(0, 3)];
        $this->setType($type);
        $this->setText($type);
        $this->displayAsStatusLabel(rand(0, 1));
    }

    public function loadHtml(): void
    {
        $this->setText('Loading...');
    }
}
