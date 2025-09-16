<?php

namespace ModulesGarden\TSSGGSModule\Components\Text;

use ModulesGarden\TSSGGSModule\Core\Components\Decorator\Decorator;

class TextBold extends Text
{
    public function __construct()
    {
        parent::__construct();

        (new Decorator($this))->font()->setBoldWeight();
    }
}