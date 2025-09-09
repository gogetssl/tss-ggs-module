<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\CollapsableBox\Widgets;

use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\CollapsableBox\Forms\CollapsableDropdownForm;

class CollapseDropdownWidget extends Widget implements AjaxComponentInterface, AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->setTitle("Collapse Example");

        $this->addElement(new CollapsableDropdownForm());
    }
}