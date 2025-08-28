<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Configuration\Pages;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Configuration\Content\Step5;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Configuration\Shared\Menu;
use ModulesGarden\TTSGGSModule\Components\Grid\Grid;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ClientAreaInterface;
use ModulesGarden\TTSGGSModule\Components\Container\Container;


class ConfigurationStep5 extends Container implements AdminAreaInterface, ClientAreaInterface
{
    public function loadHtml(): void
    {
        $grid = new Grid();
        $grid->setRows(
            [
                [[(new Menu())->getMenu(), 3], [new Step5(), 9]]
            ]
        );

        $this->addElement($grid);
    }
}
