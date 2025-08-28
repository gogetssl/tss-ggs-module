<?php

namespace ModulesGarden\TTSGGSModule\Components\LayoutWithSidebar;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ComponentsContainerTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\CssContainerTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\SizeTrait;

class LayoutWithSidebar extends AbstractComponent
{
    use SizeTrait;
    use CssContainerTrait;
    use ComponentsContainerTrait;

    public const COMPONENT = 'LayoutWithSidebar';

    public function addSidebar($sidebar): self
    {
        $this->addComponent('sidebars', $sidebar);

        return $this;
    }

    public function clearSidebars(): self
    {
        $this->setSlot('sidebars', []);

        return $this;
    }

}