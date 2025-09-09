<?php

namespace ModulesGarden\TSSGGSModule\Components\FormInputEmail;

use ModulesGarden\TSSGGSModule\Components\FormInputText\FormInputText;

class FormInputEmail extends FormInputText
{
    public function __construct()
    {
        parent::__construct();

        $this->setType('email');
        $this->setPlaceholder('email@example.com');
    }
}
