<?php

namespace ModulesGarden\TTSGGSModule\Components\Alert;

use ModulesGarden\TTSGGSModule\Core\Components\Enums\Color;

class AlertSuccess extends Alert
{
    public function __construct()
    {
        parent::__construct();

        $this->setType(Color::SUCCESS);
    }
}
