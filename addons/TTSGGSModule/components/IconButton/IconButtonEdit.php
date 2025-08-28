<?php

namespace ModulesGarden\TTSGGSModule\Components\IconButton;

use ModulesGarden\TTSGGSModule\Core\Components\Enums\Color;

/**
 * Class IconButton
 */
class IconButtonEdit extends IconButtonPrimary
{
    public function __construct()
    {
        parent::__construct();
        $this->setIcon('pencil');
    }
}
