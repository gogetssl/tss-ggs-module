<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TreeView\Forms;

use ModulesGarden\TSSGGSModule\Components\Form\Form;

class Clients extends Form
{
    public function loadHtml(): void
    {
        $this->builder->addField(new \ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TreeView\Dropdowns\Clients());
        $this->builder->addField(new \ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TreeView\Dropdowns\Services());
    }
}