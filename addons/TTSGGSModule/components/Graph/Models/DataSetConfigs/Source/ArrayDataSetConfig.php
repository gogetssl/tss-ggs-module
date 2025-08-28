<?php

namespace ModulesGarden\TTSGGSModule\Components\Graph\Models\DataSetConfigs\Source;

abstract class ArrayDataSetConfig extends AbstractDataSetConfig
{
    public function __construct(array $value)
    {
        $this->value = $value;
    }
}