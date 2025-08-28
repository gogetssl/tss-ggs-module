<?php

namespace ModulesGarden\TTSGGSModule\Components\Graph\Options;

use ModulesGarden\TTSGGSModule\Components\Graph\Source\AbstractOption;

class LegendOptions extends AbstractOption
{
    public bool $show = false;
    public string $position;
    public string $horizontalAlign;
    public array $markers;

    public function __construct(bool $show = true, string $position = 'top', string $horizontalAlign = 'center')
    {
        $this->show            = $show;
        $this->position        = $position;
        $this->horizontalAlign = $horizontalAlign;
        $this->markers         = ['shape' => "circle"];
    }

    public function getAttributes():array
    {
        return [
            'show'            => $this->show,
            'position'        => $this->position,
            'horizontalAlign' => $this->horizontalAlign,
            'markers'         => $this->markers,
        ];
    }
}