<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Widgets\ApiSettingsWidget;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Widgets\CronSettingsWidget;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Widgets\FinanceSettingsWidget;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Widgets\Sidebar;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Widgets\SslSettingsWidget;
use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Components\Grid\Grid;
use ModulesGarden\TTSGGSModule\Components\Tab\Tab;
use ModulesGarden\TTSGGSModule\Components\TabsWidget\TabsWidget;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;

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