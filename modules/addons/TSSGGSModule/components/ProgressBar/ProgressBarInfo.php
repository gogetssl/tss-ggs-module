<?php

namespace ModulesGarden\TSSGGSModule\Components\ProgressBar;

use ModulesGarden\TSSGGSModule\Core\Components\Enums\BackgroundColor;

class ProgressBarInfo extends ProgressBar
{
    public function __construct()
    {
        parent::__construct();
        $this->setType(BackgroundColor::INFO);
    }
}