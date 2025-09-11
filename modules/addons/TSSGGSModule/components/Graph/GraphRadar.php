<?php

namespace ModulesGarden\TSSGGSModule\Components\Graph;

use ModulesGarden\TSSGGSModule\Components\Graph\Source\GraphExtendedSeries;

class GraphRadar extends GraphExtendedSeries
{
    public function __construct()
    {
        parent::__construct();

        $this->setType('radar');
    }
}
