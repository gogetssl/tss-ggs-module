<?php

namespace ModulesGarden\TSSGGSModule\Components\Tagger;

use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;

class Tagger extends Dropdown
{
    public function __construct()
    {
        parent::__construct();

        $this->setMultiple(true);
        $this->setAllowToCreate(true);
    }
}
