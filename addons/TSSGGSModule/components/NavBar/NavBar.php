<?php

namespace ModulesGarden\TSSGGSModule\Components\NavBar;

use ModulesGarden\TSSGGSModule\Components\NavBarItem\NavBarItem;
use ModulesGarden\TSSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ComponentsContainerTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ToolbarTrait;

/**
 * Class Form
 */
class NavBar extends AbstractComponent
{
    use ComponentsContainerTrait;
    use ToolbarTrait;

    public const COMPONENT = 'NavBar';

    public function __construct()
    {
        parent::__construct();
        $this->withPadding();
    }

    public function withPadding($padding = 'lu-m-b-4x'): self
    {
        $this->setSlot('paddingClass', $padding);

        return $this;
    }

    public function addItem(NavBarItem $item): self
    {
        $this->addElement($item);

        return $this;
    }
}
