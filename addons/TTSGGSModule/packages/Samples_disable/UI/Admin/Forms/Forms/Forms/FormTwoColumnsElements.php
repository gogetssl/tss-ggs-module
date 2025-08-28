<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Forms\Forms\Forms;

use ModulesGarden\TTSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;


class FormTwoColumnsElements extends AbstractFormElements implements AdminAreaInterface, AjaxComponentInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->builder = BuilderCreator::twoColumns($this);
    }
}
