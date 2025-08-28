<?php

namespace ModulesGarden\TTSGGSModule\Components\DropdownMenuItem;

class DropdownMenuItemEdit extends DropdownMenuItem
{
    public function __construct()
    {
        parent::__construct();

        $this->setIcon('pencil');
    }
}
