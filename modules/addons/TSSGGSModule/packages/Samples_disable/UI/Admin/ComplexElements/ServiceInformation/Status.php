<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\ServiceInformation;

use ModulesGarden\TSSGGSModule\Components\Label\Label;
use ModulesGarden\TSSGGSModule\Core\Components\Enums\Color;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxAutoReloadInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxOnLoadInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ClientAreaInterface;
use ModulesGarden\TSSGGSModule\Core\UI\Interfaces\ClientArea;

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
