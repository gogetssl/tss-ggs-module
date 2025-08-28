<?php

namespace ModulesGarden\TTSGGSModule\Components\ProgressBar;

use ModulesGarden\TTSGGSModule\Core\Components\Enums\BackgroundColor;

class ProgressBarInfo extends ProgressBar
{
    public function __construct()
    {
        parent::__construct();
        $this->setType(BackgroundColor::INFO);
    }
}