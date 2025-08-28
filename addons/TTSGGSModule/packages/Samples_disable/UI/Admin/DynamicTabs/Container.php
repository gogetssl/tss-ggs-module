<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DynamicTabs;

use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DynamicTabs\Pages\DynamicTabs;

class Container extends \ModulesGarden\TTSGGSModule\Components\Container\Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new DynamicTabs());
    }
}