<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\CopyToTarget\Widgets;

use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\CopyToTarget\Forms\BaseForm;

class BaseWidget extends Widget implements AjaxComponentInterface, AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->setTitle("Copy To Target Sample");

        $this->addElement(new BaseForm());
    }
}