<?php

namespace ModulesGarden\TSSGGSModule\Components\TextShowHide;

use ModulesGarden\TSSGGSModule\Components\Container\Container;

class TextShowHide extends Container
{
    public const COMPONENT = 'TextShowHide';

    public function setText(string $text): self
    {
        $this->setSlot('text', $text);

        return $this;
    }
}
