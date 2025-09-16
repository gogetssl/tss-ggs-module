<?php

namespace ModulesGarden\TSSGGSModule\Components\IconButton;

use ModulesGarden\TSSGGSModule\Core\Components\Enums\Color;

/**
 * Class IconButton
 */
class IconButtonDanger extends IconButton
{
    public function __construct()
    {
        parent::__construct();
        $this->setType(Color::DANGER);
    }
}
