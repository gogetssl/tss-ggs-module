<?php

namespace ModulesGarden\TTSGGSModule\Components\Graph\Models;

use Illuminate\Contracts\Support\Arrayable;
use ModulesGarden\TTSGGSModule\Components\Graph\Options\DataSetConfigs\Source\DataSetConfigInterface;

/**
 * @deprecated
 */
class DataSetConfiguration implements Arrayable
{
    protected array $configs = [];

    public function addConfiguration(DataSetConfigInterface $dataSetConfigInterface):self
    {
        array_push($this->configs, $dataSetConfigInterface);

        return $this;
    }

    public function toArray()
    {
        $configurations = [];

        foreach ($this->configs as $config)
        {
            /**
             * @var $column DataSetConfigInterface
             */
            $configurations[$config->getName()] = $config->getValue();
        }

        return $configurations;
    }
}