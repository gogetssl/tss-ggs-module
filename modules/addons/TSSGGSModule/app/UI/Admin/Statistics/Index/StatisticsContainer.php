<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Statistics\Index;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Statistics\Index\Widgets\InvoicesWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Statistics\Index\Widgets\ProductIncomeWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Statistics\Index\Widgets\ResellersWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Statistics\Index\Widgets\TotalIncomeWidget;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Grid\Grid;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;

class StatisticsContainer extends Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $grid = new Grid();
        $grid->setRows(
            [
                [
                    [new TotalIncomeWidget(), 12]
                ],
                [
                    [new ProductIncomeWidget(), 12]
                ],
                [
                    [new ResellersWidget(), 6],
                    [new InvoicesWidget(), 6],
                ]
            ]
        );

        $this->addElement($grid);
    }
}