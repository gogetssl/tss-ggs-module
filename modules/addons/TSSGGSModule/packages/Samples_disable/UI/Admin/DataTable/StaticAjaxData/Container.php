<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\StaticAjaxData;

use ModulesGarden\TSSGGSModule\Components\Grid\Grid;

class Container extends \ModulesGarden\TSSGGSModule\Components\Container\Container
{
    public function loadHtml(): void
    {
        $grid = new Grid();
        $grid->setRows([

                [[new DataTable()]],
                [[new Widget()]],
            ]);
        $this->addElement($grid);
    }
}