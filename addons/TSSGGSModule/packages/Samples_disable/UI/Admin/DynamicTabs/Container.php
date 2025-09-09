<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DynamicTabs;

use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DynamicTabs\Pages\DynamicTabs;

class Container extends \ModulesGarden\TSSGGSModule\Components\Container\Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new DynamicTabs());
    }
}