<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Widgets\ApiSettingsWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Widgets\Sidebar;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Grid\Grid;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;

class SettingsContainer extends Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        // skin of the settings pages (resources/assets/css/shell.css)
        $this->setCss('tssggs-settings');

        if(Request::get('settings-page') == 'apiSettings')
        {
            $mainWidget = new ApiSettingsWidget();
        }
        else
        {
            $mainWidget = new ApiSettingsWidget();
        }

        $grid = new Grid();
        $grid->setRows(
            [
                [
                      [new Sidebar(), 2]   ,[$mainWidget,10]
                ]

            ]
        );

        $this->addElement($grid);


    }
}
