<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\BasicElements\Modals\Forms;

use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\Switcher\Switcher;

class Confirm extends Form
{
    public function loadHtml(): void
    {
        $this->builder->createField(Switcher::class, 'confirm');
    }
}