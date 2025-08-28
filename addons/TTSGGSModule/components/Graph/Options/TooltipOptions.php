<?php

namespace ModulesGarden\TTSGGSModule\Components\Graph\Options;

use ModulesGarden\TTSGGSModule\Components\Graph\Source\AbstractOption;

class TooltipOptions extends AbstractOption
{
    public bool $enabled;

    public function __construct(bool $enabled = true)
    {
        $this->enabled = $enabled;
    }

    public function getAttributes():array
    {
        return [
            'enabled' => $this->enabled,
        ];
    }
}