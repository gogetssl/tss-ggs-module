<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Pages;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Content\Step4;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Shared\Menu;
use ModulesGarden\TSSGGSModule\Components\Grid\Grid;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ClientAreaInterface;
use ModulesGarden\TSSGGSModule\Components\Container\Container;


class ConfigurationStep4 extends Container implements AdminAreaInterface, ClientAreaInterface
{
    public function loadHtml(): void
    {
        $grid = new Grid();
        $grid->setRows(
            [
                [[(new Menu())->getMenu(), 3], [new Step4(), 9]]
            ]
        );

        $this->addElement($grid);
    }
}
