<?php

namespace ModulesGarden\TSSGGSModule\Components\Graph;

use ModulesGarden\TSSGGSModule\Components\Graph\Source\GraphSimpleSeries;

class GraphPolarArea extends GraphSimpleSeries
{
    public function __construct()
    {
        parent::__construct();

        $this->setType('polarArea');
    }
}
