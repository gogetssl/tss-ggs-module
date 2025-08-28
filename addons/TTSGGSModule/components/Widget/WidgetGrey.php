<?php

namespace ModulesGarden\TTSGGSModule\Components\Widget;

use ModulesGarden\TTSGGSModule\Core\Components\Decorator\Decorator;

class WidgetGrey extends Widget
{
    public function __construct()
    {
        parent::__construct();

        (new Decorator($this))->background()->setGrey();
    }
}
