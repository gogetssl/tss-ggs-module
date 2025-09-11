<?php

namespace ModulesGarden\TSSGGSModule\Components\Alert;

use ModulesGarden\TSSGGSModule\Core\Components\Enums\Color;

class AlertSuccess extends Alert
{
    public function __construct()
    {
        parent::__construct();

        $this->setType(Color::SUCCESS);
    }
}
