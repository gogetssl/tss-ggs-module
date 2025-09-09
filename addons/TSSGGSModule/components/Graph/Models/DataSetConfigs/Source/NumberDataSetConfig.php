<?php

namespace ModulesGarden\TSSGGSModule\Components\Graph\Models\DataSetConfigs\Source;

abstract class NumberDataSetConfig extends AbstractDataSetConfig
{
    public function __construct(int $value)
    {
        $this->value = $value;
    }
}