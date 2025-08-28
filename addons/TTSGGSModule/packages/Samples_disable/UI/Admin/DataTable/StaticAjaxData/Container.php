<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\StaticAjaxData;

use ModulesGarden\TTSGGSModule\Components\Grid\Grid;

class Container extends \ModulesGarden\TTSGGSModule\Components\Container\Container
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