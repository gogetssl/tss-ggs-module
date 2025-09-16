<?php

namespace ModulesGarden\TSSGGSModule\Components\Badge;

use ModulesGarden\TSSGGSModule\Core\Components\Enums\Color;

/**
 * Class Form
 */
class BadgeWarning extends Badge
{
    public function __construct()
    {
        parent::__construct();
        $this->setType(Color::WARNING);
    }
}
