<?php

namespace ModulesGarden\TSSGGSModule\Components\Label;

use ModulesGarden\TSSGGSModule\Core\Components\Enums\Color;

/**
 * Class Form
 */
class LabelInfo extends Label
{
    public function __construct()
    {
        parent::__construct();
        $this->setType(Color::INFO);
    }
}
