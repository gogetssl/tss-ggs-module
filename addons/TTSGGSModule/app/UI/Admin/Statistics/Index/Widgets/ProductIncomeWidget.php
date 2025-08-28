<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Statistics\Index\Widgets;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Statistics\Index\Graphs\ProductIncomeGraph;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;


class ProductIncomeWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));
        $graph = new ProductIncomeGraph();
        $this->addElement($graph);
    }
}