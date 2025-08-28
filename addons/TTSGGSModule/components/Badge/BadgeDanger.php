<?php

namespace ModulesGarden\TTSGGSModule\Components\Badge;

use ModulesGarden\TTSGGSModule\Core\Components\Enums\Color;

/**
 * Class Form
 */
class BadgeDanger extends Badge
{
    public function __construct()
    {
        parent::__construct();
        $this->setType(Color::DANGER);
    }
}
