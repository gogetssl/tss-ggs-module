<?php

namespace ModulesGarden\TTSGGSModule\Components\Graph;

use ModulesGarden\TTSGGSModule\Components\Graph\Source\GraphSimpleSeries;

class GraphPolarArea extends GraphSimpleSeries
{
    public function __construct()
    {
        parent::__construct();

        $this->setType('polarArea');
    }
}
