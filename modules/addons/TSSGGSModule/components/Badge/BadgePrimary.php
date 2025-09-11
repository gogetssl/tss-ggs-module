<?php

namespace ModulesGarden\TSSGGSModule\Components\Badge;

use ModulesGarden\TSSGGSModule\Core\Components\Enums\Color;

/**
 * Class Form
 */
class BadgePrimary extends Badge
{
    public function __construct()
    {
        parent::__construct();

        $this->setType(Color::PRIMARY);
    }
}
