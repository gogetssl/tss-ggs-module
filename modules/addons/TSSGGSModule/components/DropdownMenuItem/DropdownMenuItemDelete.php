<?php

namespace ModulesGarden\TSSGGSModule\Components\DropdownMenuItem;

class DropdownMenuItemDelete extends DropdownMenuItem
{
    public function __construct()
    {
        parent::__construct();

        $this->setIcon('delete');
    }
}
