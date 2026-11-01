<?php

namespace ModulesGarden\TSSGGSModule\Components\Widget;

use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\TitleTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ToolbarTrait;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ComponentContainerInterface;

class Widget extends Container implements ComponentContainerInterface
{
    use TitleTrait;
    use ToolbarTrait;

    public const COMPONENT = 'Widget';

    public function setIcon(string $icon)
    {
        // inline SVG; the class only for a name that has no SVG yet
        $svg = Icons::svg($icon);
        $this->setSlot('icon', $svg !== '' ? $svg : 'mdi mdi-' . $icon);
    }
}


