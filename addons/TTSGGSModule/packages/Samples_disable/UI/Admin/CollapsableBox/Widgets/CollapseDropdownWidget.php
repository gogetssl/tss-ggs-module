<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\CollapsableBox\Widgets;

use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\CollapsableBox\Forms\CollapsableDropdownForm;

class CollapseDropdownWidget extends Widget implements AjaxComponentInterface, AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->setTitle("Collapse Example");

        $this->addElement(new CollapsableDropdownForm());
    }
}