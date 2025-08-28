<?php

namespace ModulesGarden\TTSGGSModule\Components\Graph;

use ModulesGarden\TTSGGSModule\Components\Graph\Source\InlineGraph;

class InlineGraphArea extends InlineGraph
{
    public function __construct()
    {
        parent::__construct();

        $this->setType('area');
    }
}