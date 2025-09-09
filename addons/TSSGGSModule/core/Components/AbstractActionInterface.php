<?php

namespace ModulesGarden\TSSGGSModule\Core\Components;

use JsonSerializable;

class AbstractActionInterface implements JsonSerializable, \ModulesGarden\TSSGGSModule\Core\Contracts\Components\ActionInterface
{
    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return [];
    }
}
