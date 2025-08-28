<?php

namespace ModulesGarden\TTSGGSModule\Components\Graph;

use ModulesGarden\TTSGGSModule\Components\Graph\Source\GraphExtendedSeries;

class GraphRadar extends GraphExtendedSeries
{
    public function __construct()
    {
        parent::__construct();

        $this->setType('radar');
    }
}
