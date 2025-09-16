<?php

namespace ModulesGarden\TSSGGSModule\Components\Widget;

use ModulesGarden\TSSGGSModule\Core\Components\Decorator\Decorator;

class WidgetGrey extends Widget
{
    public function __construct()
    {
        parent::__construct();

        (new Decorator($this))->background()->setGrey();
    }
}
