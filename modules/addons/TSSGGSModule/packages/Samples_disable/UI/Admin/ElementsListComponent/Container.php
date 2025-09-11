<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ElementsListComponent;

use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ElementsListComponent\ElementsLists\ElementsList;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ElementsListComponent\ElementsLists\WordPressSampleList;

class Container extends \ModulesGarden\TSSGGSModule\Components\Container\Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new WordPressSampleList());
        $this->addElement(new ElementsList());
    }
}