<?php

namespace ModulesGarden\TTSGGSModule\Core\Components;

use JsonSerializable;

class AbstractActionInterface implements JsonSerializable, \ModulesGarden\TTSGGSModule\Core\Contracts\Components\ActionInterface
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
