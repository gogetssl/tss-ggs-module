<?php

namespace ModulesGarden\TTSGGSModule\Components\Graph;

class GraphLine extends Graph
{
    public function __construct()
    {
        parent::__construct();

        $this->setType('line');
    }
}
