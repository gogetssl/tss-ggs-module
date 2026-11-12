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

    /** A second line under the title, in the widget header */
    public function setSubtitle(string $subtitle)
    {
        $this->setSlot('subtitle', $subtitle);

        return $this;
    }

    public function setIcon(string $icon)
    {
        // inline SVG; the class only for a name that has no SVG yet
        $svg = Icons::svg($icon);
        $this->setSlot('icon', $svg !== '' ? $svg : 'mdi mdi-' . $icon);
    }
}


