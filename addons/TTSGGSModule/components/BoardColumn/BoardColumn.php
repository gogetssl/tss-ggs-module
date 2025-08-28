<?php

namespace ModulesGarden\TTSGGSModule\Components\BoardColumn;

use ModulesGarden\TTSGGSModule\Components\Container\Container;

class BoardColumn extends Container
{
    public const COMPONENT = 'BoardColumn';

    public function setName(string $name): self
    {
        $this->setSlot('name', $name);

        return $this;
    }
}
