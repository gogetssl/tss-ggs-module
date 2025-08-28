<?php

namespace ModulesGarden\TTSGGSModule\Components\Tagger;

use ModulesGarden\TTSGGSModule\Components\Dropdown\Dropdown;

class Tagger extends Dropdown
{
    public function __construct()
    {
        parent::__construct();

        $this->setMultiple(true);
        $this->setAllowToCreate(true);
    }
}
