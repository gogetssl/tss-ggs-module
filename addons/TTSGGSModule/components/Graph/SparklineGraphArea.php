<?php

namespace ModulesGarden\TTSGGSModule\Components\Graph;

use ModulesGarden\TTSGGSModule\Components\Graph\Source\SparklineGraph;

class SparklineGraphArea extends SparklineGraph
{
    public function __construct()
    {
        parent::__construct();

        $this->setType("area");
    }
}