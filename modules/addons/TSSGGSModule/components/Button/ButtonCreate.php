<?php

namespace ModulesGarden\TSSGGSModule\Components\Button;

class ButtonCreate extends ButtonPrimary
{
    public function __construct()
    {
        parent::__construct();

        $this->setIcon('plus');
    }
}
