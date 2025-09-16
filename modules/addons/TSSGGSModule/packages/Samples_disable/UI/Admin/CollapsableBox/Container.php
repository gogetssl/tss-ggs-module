<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\CollapsableBox;

use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\CollapsableBox\Widgets\AccordionWidget;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\CollapsableBox\Widgets\BaseWidget;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\CollapsableBox\Widgets\CollapseDropdownWidget;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\CollapsableBox\Widgets\CollapseGroupWidget;

class Container extends \ModulesGarden\TSSGGSModule\Components\Container\Container implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new CollapseDropdownWidget());
        $this->addElement(new BaseWidget());
        $this->addElement(new AccordionWidget());
        $this->addElement(new CollapseGroupWidget());
    }
}