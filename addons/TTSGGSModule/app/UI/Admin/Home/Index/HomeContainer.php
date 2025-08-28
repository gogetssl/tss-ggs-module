<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Configuration\Content\Step1;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Configuration\Shared\Menu;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index\Widgets\AnnouncementsWidget;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index\Widgets\OverviewWidget;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index\Widgets\PartnerWidget;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index\Widgets\ReportingWidget;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index\Widgets\SupportWidget;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index\Widgets\SystemCheckWidget;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index\Widgets\UpgradeWidget;
use ModulesGarden\TTSGGSModule\Components\Alert\Alert;
use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Components\Container\ContainerColumn;
use ModulesGarden\TTSGGSModule\Components\Grid\Grid;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;

class HomeContainer extends Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $grid = new Grid();
        $grid->setRows(
            [
                //[
                //    [new UpgradeWidget(), 12]
                //],
                [
                    [
                        (new Container())
                            ->addElement(new OverviewWidget())
                            ->addElement(new AnnouncementsWidget()),
                        8
                    ],
                    [
                        (new Container())
                            ->addElement(new ReportingWidget())
                            ->addElement(new SystemCheckWidget())
                            ->addElement(new PartnerWidget())
                            ->addElement(new SupportWidget()),
                        4
                    ],
                ]
            ]
        );

        $this->addElement($grid);
    }
}