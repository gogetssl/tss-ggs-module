<?php

namespace ModulesGarden\TTSGGSModule\Components\Label;

use ModulesGarden\TTSGGSModule\Core\Components\Enums\Color;

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
