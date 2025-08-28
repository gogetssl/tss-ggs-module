<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Statistics\Index\Widgets;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Statistics\Index\Graphs\TotalIncomeGraph;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;


class TotalIncomeWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));
        $graph = new TotalIncomeGraph();
        $this->addElement($graph);
    }
}