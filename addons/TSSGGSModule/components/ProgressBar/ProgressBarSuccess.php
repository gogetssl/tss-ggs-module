<?php

namespace ModulesGarden\TSSGGSModule\Components\ProgressBar;

use ModulesGarden\TSSGGSModule\Core\Components\Enums\BackgroundColor;

class ProgressBarSuccess extends ProgressBar
{
    public function __construct()
    {
        parent::__construct();
        $this->setType(BackgroundColor::SUCCESS);
    }
}