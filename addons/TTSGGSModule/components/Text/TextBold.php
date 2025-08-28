<?php

namespace ModulesGarden\TTSGGSModule\Components\Text;

use ModulesGarden\TTSGGSModule\Core\Components\Decorator\Decorator;

class TextBold extends Text
{
    public function __construct()
    {
        parent::__construct();

        (new Decorator($this))->font()->setBoldWeight();
    }
}