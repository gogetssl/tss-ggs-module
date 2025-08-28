<?php

namespace ModulesGarden\TTSGGSModule\Components\IconButton;

use ModulesGarden\TTSGGSModule\Core\Components\Enums\Color;

/**
 * Class IconButton
 */
class IconButtonWarning extends IconButton
{
    public function __construct()
    {
        parent::__construct();
        $this->setType(Color::WARNING);
    }
}
