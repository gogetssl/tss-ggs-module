<?php

namespace ModulesGarden\TTSGGSModule\Components\Graph;

use ModulesGarden\TTSGGSModule\Components\Graph\Source\GraphSimpleSeries;

class GraphPie extends GraphSimpleSeries
{
    public function __construct()
    {
        parent::__construct();

        $this->setType('pie');
    }
}
