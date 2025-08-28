<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\Libs\ConfigurableOptionsGroups;

use ModulesGarden\TTSGGSModule\Packages\Product\Libs\ConfigurableOptions\AbstractConfigurableOption;

class ConfigurableOptionsGroup
{
    protected string $name;
    protected array $options;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): self
    {
        foreach ($options as $option)
        {
            $this->addOption($option);
        }

        return $this;
    }

    public function addOption(AbstractConfigurableOption $option)
    {
        $this->options[] = $option;
    }
}