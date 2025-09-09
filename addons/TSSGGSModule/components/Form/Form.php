<?php

namespace ModulesGarden\TSSGGSModule\Components\Form;

use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ToolbarTrait;

class Form extends AbstractForm
{
    use ToolbarTrait;

    public function __construct()
    {
        parent::__construct();

        $this->builder = BuilderCreator::simple($this);
    }
}