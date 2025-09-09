<?php

namespace ModulesGarden\TSSGGSModule\Components\Graph;

use ModulesGarden\TSSGGSModule\Components\Graph\Source\InlineGraph;

class InlineGraphLine extends InlineGraph
{
    public function __construct()
    {
        parent::__construct();

        $this->setType('line');
    }
}