<?php

namespace ModulesGarden\TTSGGSModule\Components\Widget;

use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\TitleTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ToolbarTrait;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ComponentContainerInterface;

class Widget extends Container implements ComponentContainerInterface
{
    use TitleTrait;
    use ToolbarTrait;

    public const COMPONENT = 'Widget';

    public function setIcon(string $icon)
    {
        $this->setSlot('icon', 'mdi mdi-' . $icon);
    }
}


