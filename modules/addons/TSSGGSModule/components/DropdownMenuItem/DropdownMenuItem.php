<?php

namespace ModulesGarden\TSSGGSModule\Components\DropdownMenuItem;

use ModulesGarden\TSSGGSModule\Components\Button\Button;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\DropdownMenuItemInterface;

class DropdownMenuItem extends Button implements DropdownMenuItemInterface
{
    public const COMPONENT = 'DropdownMenuItem';

    public function __construct()
    {
        parent::__construct();

        $this->setTranslations([
            'title',
        ]);
    }
}
