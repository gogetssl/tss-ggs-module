<?php

namespace ModulesGarden\TSSGGSModule\Components\BoardColumn;

use ModulesGarden\TSSGGSModule\Components\Container\Container;

class BoardColumn extends Container
{
    public const COMPONENT = 'BoardColumn';

    public function setName(string $name): self
    {
        $this->setSlot('name', $name);

        return $this;
    }
}
