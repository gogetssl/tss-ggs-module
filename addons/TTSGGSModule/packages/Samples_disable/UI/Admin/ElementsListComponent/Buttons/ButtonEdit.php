<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ElementsListComponent\Buttons;

use ModulesGarden\TTSGGSModule\Components\IconButton\IconButton;

class ButtonEdit extends IconButton
{
    public function __construct()
    {
        parent::__construct();
        $this->setIcon('pencil');
    }
}