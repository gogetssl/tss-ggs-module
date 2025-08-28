<?php

namespace ModulesGarden\TTSGGSModule\Components\Alert;

use ModulesGarden\TTSGGSModule\Core\Components\Enums\Color;

class AlertInfo extends Alert
{
    public function __construct()
    {
        parent::__construct();

        $this->setType(Color::INFO);
    }
}
