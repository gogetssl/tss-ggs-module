<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TreeView\Forms;

use ModulesGarden\TTSGGSModule\Components\Form\Form;

class Clients extends Form
{
    public function loadHtml(): void
    {
        $this->builder->addField(new \ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TreeView\Dropdowns\Clients());
        $this->builder->addField(new \ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TreeView\Dropdowns\Services());
    }
}