<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable;

use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;

class Container extends \ModulesGarden\TSSGGSModule\Components\Container\Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
//        $this->addElement(new \ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\QueryDataProvider\DataTable());
//        $this->addElement(new \ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\ArrayDataProvider\DataTable());
//        $this->addElement(new \ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\Tabs\TabsWidget());
        $this->addElement(new StaticAjaxData\Container());
    }
}