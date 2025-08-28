<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Graphs\Graphs;

use ModulesGarden\TTSGGSModule\Components\Graph\Series\ExtendedSeries;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;

class GraphLine extends \ModulesGarden\TTSGGSModule\Components\Graph\GraphLine implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        parent::loadHtml();

        $this->setTitle("Graph Line");
    }

    public function loadData(): void
    {
        $this->setLabels(['day 1', 'day 2', 'day 3']);

        for ($i = 1 ; $i <= 3 ; $i++)
        {
            $this->addSeries(new ExtendedSeries('Data Set ' . $i, [rand(0, 10), rand(0, 10), rand(0, 10)]));
        }
    }
}
