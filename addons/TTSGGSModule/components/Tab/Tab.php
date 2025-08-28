<?php

namespace ModulesGarden\TTSGGSModule\Components\Tab;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ComponentsContainerTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ContentTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\TitleTrait;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ComponentContainerInterface;

/**
 * Class Tab
 */
class Tab extends AbstractComponent implements ComponentContainerInterface
{
    use ComponentsContainerTrait;
    use TitleTrait;
    use ContentTrait;

    public const COMPONENT = 'Tab';

    public function setActive(bool $active = true):self
    {
        $this->setSlot('isActive', $active);

        return $this;
    }
}
