<?php

namespace ModulesGarden\TTSGGSModule\Components\Form;

use ModulesGarden\TTSGGSModule\Components\Form\Builder\BuilderCreator;

abstract class AbstractFormOneColumn extends AbstractForm
{
    public function __construct()
    {
        parent::__construct();

        $this->builder = BuilderCreator::oneColumn($this);
    }
}