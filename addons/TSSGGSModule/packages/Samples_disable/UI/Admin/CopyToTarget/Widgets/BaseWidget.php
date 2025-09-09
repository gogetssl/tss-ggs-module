<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\CopyToTarget\Widgets;

use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\CopyToTarget\Forms\BaseForm;

class BaseWidget extends Widget implements AjaxComponentInterface, AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->setTitle("Copy To Target Sample");

        $this->addElement(new BaseForm());
    }
}