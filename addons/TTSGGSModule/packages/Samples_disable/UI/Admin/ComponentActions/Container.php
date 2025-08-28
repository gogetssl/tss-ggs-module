<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComponentActions;

use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\CollapsableBox\Widgets\AccordionWidget;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\CollapsableBox\Widgets\BaseWidget;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComponentActions\DownloadFile\Widgets\Widget;

class Container extends \ModulesGarden\TTSGGSModule\Components\Container\Container implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new Widget());
    }
}