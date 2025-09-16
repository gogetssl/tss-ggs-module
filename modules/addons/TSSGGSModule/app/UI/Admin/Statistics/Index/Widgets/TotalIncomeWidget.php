<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Statistics\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Statistics\Index\Graphs\TotalIncomeGraph;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;


class TotalIncomeWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));
        $graph = new TotalIncomeGraph();
        $this->addElement($graph);
    }
}