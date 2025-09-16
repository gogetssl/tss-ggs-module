<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComponentActions;

use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\CollapsableBox\Widgets\AccordionWidget;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\CollapsableBox\Widgets\BaseWidget;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComponentActions\DownloadFile\Widgets\Widget;

class Container extends \ModulesGarden\TSSGGSModule\Components\Container\Container implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new Widget());
    }
}