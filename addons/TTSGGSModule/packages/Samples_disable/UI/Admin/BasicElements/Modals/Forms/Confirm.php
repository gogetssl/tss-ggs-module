<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\BasicElements\Modals\Forms;

use ModulesGarden\TTSGGSModule\Components\Form\Form;
use ModulesGarden\TTSGGSModule\Components\Switcher\Switcher;

class Confirm extends Form
{
    public function loadHtml(): void
    {
        $this->builder->createField(Switcher::class, 'confirm');
    }
}