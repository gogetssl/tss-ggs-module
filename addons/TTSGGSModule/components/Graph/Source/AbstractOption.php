<?php

namespace ModulesGarden\TTSGGSModule\Components\Graph\Source;

use Illuminate\Contracts\Support\Arrayable;

abstract class AbstractOption implements Arrayable
{
    protected array $additionalParams = [];

    abstract public function getAttributes():array;

    public function addAdditionalParameter(string $name, $value):self
    {
        $this->additionalParams[$name] = $value;

        return $this;
    }

    public final function toArray(): array
    {
        return array_merge($this->getAttributes(), $this->additionalParams);
    }
}