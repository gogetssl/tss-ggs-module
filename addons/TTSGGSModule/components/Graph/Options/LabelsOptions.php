<?php

namespace ModulesGarden\TTSGGSModule\Components\Graph\Options;

use Illuminate\Contracts\Support\Arrayable;

class LabelsOptions implements Arrayable
{
    public array $labels;

    public function __construct(array $labels = [])
    {
        $this->labels = $labels;
    }

    public function toArray(): array
    {
        return $this->labels;
    }
}