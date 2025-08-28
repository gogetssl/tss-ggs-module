<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ElementsListComponent;

use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ElementsListComponent\ElementsLists\ElementsList;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ElementsListComponent\ElementsLists\WordPressSampleList;

class Container extends \ModulesGarden\TTSGGSModule\Components\Container\Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new WordPressSampleList());
        $this->addElement(new ElementsList());
    }
}