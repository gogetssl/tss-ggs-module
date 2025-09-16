<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Widgets\ApiSettingsWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Widgets\CronSettingsWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Widgets\FinanceSettingsWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Widgets\Sidebar;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Widgets\SslSettingsWidget;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Grid\Grid;
use ModulesGarden\TSSGGSModule\Components\Tab\Tab;
use ModulesGarden\TSSGGSModule\Components\TabsWidget\TabsWidget;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;

class SettingsContainer extends Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        if(Request::get('settings-page') == 'apiSettings')
        {
            $mainWidget = new ApiSettingsWidget();
        }
        elseif(Request::get('settings-page') == 'sslSettings')
        {
            $mainWidget = new SslSettingsWidget();
        }
        elseif(Request::get('settings-page') == 'financeSettings')
        {
            $mainWidget = new FinanceSettingsWidget();
        }
        elseif(Request::get('settings-page') == 'cronSettings')
        {
            $mainWidget = new CronSettingsWidget();
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