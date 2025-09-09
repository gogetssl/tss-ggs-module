<?php

namespace ModulesGarden\TSSGGSModule\Components\Form;

use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;

abstract class AbstractFormOneColumn extends AbstractForm
{
    public function __construct()
    {
        parent::__construct();

        $this->builder = BuilderCreator::oneColumn($this);
    }
}