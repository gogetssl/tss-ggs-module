<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Statistics\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Statistics\Index\Graphs\ProductIncomeGraph;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;


class ProductIncomeWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));
        $graph = new ProductIncomeGraph();
        $this->addElement($graph);
    }
}