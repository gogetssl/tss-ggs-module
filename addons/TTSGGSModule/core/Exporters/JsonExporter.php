<?php

namespace ModulesGarden\TTSGGSModule\Core\Exporters;

use Illuminate\Contracts\Support\Arrayable;
use Stringable;

class JsonExporter extends BaseExporter
{
    public function get(): string
    {
        if ($this->dataSet instanceof Arrayable)
        {
            return json_encode($this->dataSet->toArray());
        }

        if ($this->dataSet instanceof Stringable)
        {
            return $this->dataSet->__toString();
        }

        throw new \Exception("Wrong data type provided");
    }
}