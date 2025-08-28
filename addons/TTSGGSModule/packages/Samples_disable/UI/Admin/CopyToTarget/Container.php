<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\CopyToTarget;

use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\CopyToTarget\Widgets\BaseWidget;

class Container extends \ModulesGarden\TTSGGSModule\Components\Container\Container implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new BaseWidget());
    }
}