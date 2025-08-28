<?php

namespace ModulesGarden\TTSGGSModule\Components\NavBarItem;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ComponentsContainerTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\TitleTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\UrlTrait;

/**
 * Class Form
 */
class NavBarItem extends AbstractComponent
{
    use TitleTrait;
    use UrlTrait;
    use ComponentsContainerTrait;

    public const COMPONENT = 'NavBarItem';

 

    public function setActive(bool $active): self
    {
        $this->setSlot('active', $active);

        return $this;
    }

    public function setIcon(string $icon): self
    {
        $this->setSlot('icon', $icon);

        return $this;
    }



    public function addItem(NavBarItem $item): self
    {
        $this->addElement($item);

        return $this;
    }
}
