<?php

namespace ModulesGarden\TTSGGSModule\Components\Link;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\UrlTrait;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ComponentInterface;

class Link extends AbstractComponent
{
    use UrlTrait;

    public const COMPONENT = 'Link';

    public function setTitle(string|ComponentInterface $title): self
    {
        $this->setSlot('title', $title);

        return $this;
    }
}