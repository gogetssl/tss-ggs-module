<?php

namespace ModulesGarden\TSSGGSModule\Components\FormGroup;

use ModulesGarden\TSSGGSModule\Core\Components\Decorator\Decorator;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\FormFieldInterface;

class FormGroupFullWidth extends FormGroup implements FormFieldInterface
{
    public function __construct()
    {
        parent::__construct();

        /*(new Decorator($this))->columns()->one();*/
    }
}
