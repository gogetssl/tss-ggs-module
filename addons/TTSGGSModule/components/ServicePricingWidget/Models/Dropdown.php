<?php

namespace ModulesGarden\TTSGGSModule\Components\ServicePricingWidget\Models;

use ModulesGarden\TTSGGSModule\Core\Components\Traits\OptionsTrait;

class Dropdown extends Input
{
    use OptionsTrait;

    protected array $options = [];

    public function __construct(string $name = "", string $value = "", string $title = '', array $options = [])
    {
        parent::__construct($name);

        $this->setValue($value);
        $this->setTitle($title);
        $this->setOptions($options);
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function toArray():array
    {
        return array_merge(
            parent::toArray(),
            [ 'options' => $this->convertOptions($this->options)]
        );
    }
}