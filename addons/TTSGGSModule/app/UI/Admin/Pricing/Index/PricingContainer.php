<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Pricing\Index;


use ModulesGarden\TTSGGSModule\App\UI\Admin\Pricing\Index\DataTables\Pricing\ArrayDataProvider\DataTable;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Pricing\Index\DataTables\Pricing\Widgets\CurrencyWidget;
use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Components\Grid\Grid;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;

class PricingContainer extends Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $grid = new Grid();
        $grid->setRows(
            [
                [
                    [new CurrencyWidget(), 12]
                ],
                [
                    [new DataTable(), 12]
                ],

            ]
        );

        $this->addElement($grid);

        //$this->addElement(new DataTable());
    }
}