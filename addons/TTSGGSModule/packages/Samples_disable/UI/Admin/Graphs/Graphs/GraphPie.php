<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Graphs\Graphs;

use ModulesGarden\TTSGGSModule\Components\Graph\Series\SimpleSeries;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;

class GraphPie extends \ModulesGarden\TTSGGSModule\Components\Graph\GraphPie implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        parent::loadHtml();
        $this->setTitle('Graph Pie');
    }

    public function loadData(): void
    {
        $this->setLabels(['day 1', 'day 2', 'day 3']);

        for ($i = 1 ; $i <= 3 ; $i++)
        {
            $this->addSeries(new SimpleSeries(rand(0, 10)));
        }
    }
}
