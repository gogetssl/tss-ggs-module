<?php

namespace ModulesGarden\TTSGGSModule\Components\FormGroup;

use ModulesGarden\TTSGGSModule\Core\Components\Decorator\Decorator;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\FormFieldInterface;

class FormGroupFullWidth extends FormGroup implements FormFieldInterface
{
    public function __construct()
    {
        parent::__construct();

        /*(new Decorator($this))->columns()->one();*/
    }
}
