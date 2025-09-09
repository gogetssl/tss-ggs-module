<?php

namespace ModulesGarden\TSSGGSModule\Components\Graph;

use ModulesGarden\TSSGGSModule\Components\Graph\Source\SparklineGraph;

class SparklineGraphLine extends SparklineGraph
{
    public function __construct()
    {
        parent::__construct();

        $this->setSlot("line");
    }
}