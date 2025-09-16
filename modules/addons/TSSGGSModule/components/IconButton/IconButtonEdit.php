<?php

namespace ModulesGarden\TSSGGSModule\Components\IconButton;

use ModulesGarden\TSSGGSModule\Core\Components\Enums\Color;

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
