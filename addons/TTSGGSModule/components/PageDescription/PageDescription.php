<?php

namespace ModulesGarden\TTSGGSModule\Components\PageDescription;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ImageTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\TitleTrait;

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
