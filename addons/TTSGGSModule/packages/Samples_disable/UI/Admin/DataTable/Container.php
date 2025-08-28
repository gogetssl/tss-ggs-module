<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable;

use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;

class Container extends \ModulesGarden\TTSGGSModule\Components\Container\Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
//        $this->addElement(new \ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\QueryDataProvider\DataTable());
//        $this->addElement(new \ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\ArrayDataProvider\DataTable());
//        $this->addElement(new \ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Tabs\TabsWidget());
        $this->addElement(new StaticAjaxData\Container());
    }
}