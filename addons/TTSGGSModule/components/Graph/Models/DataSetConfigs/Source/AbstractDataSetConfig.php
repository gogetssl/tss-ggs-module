<?php

namespace ModulesGarden\TTSGGSModule\Components\Graph\Models\DataSetConfigs\Source;

abstract class AbstractDataSetConfig implements DataSetConfigInterface
{
    protected $value;

    public function getName(): string
    {
        return lcfirst((new \ReflectionClass($this))->getShortName());
    }

    public function getValue()
    {
        return $this->value;
    }
}