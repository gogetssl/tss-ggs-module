<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Statistics\Index;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Statistics\Index\Widgets\InvoicesWidget;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Statistics\Index\Widgets\ProductIncomeWidget;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Statistics\Index\Widgets\ResellersWidget;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Statistics\Index\Widgets\TotalIncomeWidget;
use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Components\Grid\Grid;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;

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