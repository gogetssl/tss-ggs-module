<?php

namespace ModulesGarden\TSSGGSModule\Components\LayoutWithSidebar;

use ModulesGarden\TSSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ComponentsContainerTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\CssContainerTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\SizeTrait;

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