<?php

namespace ModulesGarden\TSSGGSModule\Components\Link;

use ModulesGarden\TSSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\UrlTrait;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ComponentInterface;

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