<?php

namespace ModulesGarden\TTSGGSModule\Components\DropdownMenuItem;

use ModulesGarden\TTSGGSModule\Components\Button\Button;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\DropdownMenuItemInterface;

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
