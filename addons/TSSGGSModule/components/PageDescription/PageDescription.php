<?php

namespace ModulesGarden\TSSGGSModule\Components\PageDescription;

use ModulesGarden\TSSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ImageTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\TitleTrait;

/**
 * Class Form
 */
class PageDescription extends AbstractComponent
{
    use ImageTrait;
    use TitleTrait;

    public const COMPONENT = 'PageDescription';

    public function setContent(string $content): self
    {
        $this->setSlot('content', $content);

        return $this;
    }
}
