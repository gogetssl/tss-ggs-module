<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Pricing\Index;


use ModulesGarden\TSSGGSModule\App\UI\Admin\Pricing\Index\DataTables\Pricing\ArrayDataProvider\DataTable;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Pricing\Index\DataTables\Pricing\Widgets\CurrencyWidget;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Grid\Grid;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;

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