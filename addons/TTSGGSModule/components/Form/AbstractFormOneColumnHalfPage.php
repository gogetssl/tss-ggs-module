<?php

namespace ModulesGarden\TTSGGSModule\Components\Form;

use ModulesGarden\TTSGGSModule\Components\Form\Builder\BuilderCreator;

abstract class AbstractFormOneColumnHalfPage extends AbstractForm
{
    public function __construct()
    {
        parent::__construct();

        $this->builder = BuilderCreator::oneColumnHalfPage($this);
    }
}