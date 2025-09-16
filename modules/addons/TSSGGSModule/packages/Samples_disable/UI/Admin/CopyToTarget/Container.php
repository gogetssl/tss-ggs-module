<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\CopyToTarget;

use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\CopyToTarget\Widgets\BaseWidget;

class Container extends \ModulesGarden\TSSGGSModule\Components\Container\Container implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new BaseWidget());
    }
}