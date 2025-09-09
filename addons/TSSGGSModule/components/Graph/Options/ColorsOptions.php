<?php

namespace ModulesGarden\TSSGGSModule\Components\Graph\Options;

use Illuminate\Contracts\Support\Arrayable;
use ModulesGarden\TSSGGSModule\Components\Graph\Constants\Colors;

class ColorsOptions implements Arrayable
{
    public array $colors;

    public function __construct()
    {
        $this->colors = Colors::COLORS_SET;
    }

    public function toArray(): array
    {
        return $this->colors;
    }
}