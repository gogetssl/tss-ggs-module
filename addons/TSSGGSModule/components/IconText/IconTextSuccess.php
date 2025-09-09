<?php

namespace ModulesGarden\TSSGGSModule\Components\IconText;

use ModulesGarden\TSSGGSModule\Core\Components\Enums\Type;

class IconTextSuccess extends IconText
{
    public function __construct()
    {
        parent::__construct();
        $this->setType(Type::SUCCESS);
    }
}