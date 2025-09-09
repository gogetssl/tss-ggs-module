<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Content\Step1;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Shared\Menu;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\AnnouncementsWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\OverviewWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\PartnerWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\ReportingWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\SupportWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\SystemCheckWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\UpgradeWidget;
use ModulesGarden\TSSGGSModule\Components\Alert\Alert;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Container\ContainerColumn;
use ModulesGarden\TSSGGSModule\Components\Grid\Grid;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;

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