<?php

namespace ModulesGarden\TTSGGSModule\Core\Exporters\Source;

class BaseDataModel implements DataModelInterface
{
    protected array $customHeaders = [];

    public function setCustomHeaders(array $headers):void
    {
        $this->customHeaders = $headers;
    }

    protected function combineHeaders(array $headers): array
    {
        return array_map(function($value){
            return $this->customHeaders[$value] ?: $value;
        }, $headers);
    }
}