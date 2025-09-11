<?php

namespace ModulesGarden\TSSGGSModule\Components\IconButton;

use ModulesGarden\TSSGGSModule\Core\Components\Enums\Color;

/**
 * Class IconButton
 */
class IconButtonSuccess extends IconButton
{
    public function __construct()
    {
        parent::__construct();
        $this->setType(Color::SUCCESS);
    }
}
