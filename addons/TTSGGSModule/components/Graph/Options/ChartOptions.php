<?php

namespace ModulesGarden\TTSGGSModule\Components\Graph\Options;

use ModulesGarden\TTSGGSModule\Components\Graph\Source\AbstractOption;

class ChartOptions extends AbstractOption
{
    public string $type;
    public null|int|string $height = null;
    public null|int|string $width = null;
    public array $toolbar = ['show' => false];
    public array $zoom = ['enabled' => false];

    public function __construct(string $type = 'line')
    {
        $this->type = $type;
    }

    public function getAttributes():array
    {
        return array_filter([
            'type'    => $this->type,
            'height'  => $this->height,
            'width'   => $this->width,
            'toolbar' => $this->toolbar,
            'zoom'    => $this->zoom,
        ]);
    }
}