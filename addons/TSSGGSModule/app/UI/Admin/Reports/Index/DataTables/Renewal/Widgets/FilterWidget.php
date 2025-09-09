<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\Renewal\Widgets;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\Renewal\Forms\FilterForm;
use ModulesGarden\TSSGGSModule\Components\Grid\Grid;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;

class FilterWidget extends Widget
{
    public function loadHtml(): void
    {
        $grid = new Grid();
        $grid->setRows(
            [
                [
                    [new FilterForm(), 6]
                ]
            ]
        );

        $this->addElement($grid);
    }
}