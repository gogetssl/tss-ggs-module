<?php

namespace ModulesGarden\TTSGGSModule\Components\FormInputEmail;

use ModulesGarden\TTSGGSModule\Components\FormInputText\FormInputText;

class FormInputEmail extends FormInputText
{
    public function __construct()
    {
        parent::__construct();

        $this->setType('email');
        $this->setPlaceholder('email@example.com');
    }
}
