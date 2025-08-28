<?php

namespace ModulesGarden\TTSGGSModule\Components\Form;

use ModulesGarden\TTSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ToolbarTrait;

class Form extends AbstractForm
{
    use ToolbarTrait;

    public function __construct()
    {
        parent::__construct();

        $this->builder = BuilderCreator::simple($this);
    }
}