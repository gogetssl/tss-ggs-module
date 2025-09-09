<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ElementsListComponent\Buttons;

use ModulesGarden\TSSGGSModule\Components\IconButton\IconButton;

class ButtonTime extends IconButton
{
    public function __construct()
    {
        parent::__construct();
        $this->setIcon('time-restore');
    }
}