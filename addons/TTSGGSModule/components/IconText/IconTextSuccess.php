<?php

namespace ModulesGarden\TTSGGSModule\Components\IconText;

use ModulesGarden\TTSGGSModule\Core\Components\Enums\Type;

class IconTextSuccess extends IconText
{
    public function __construct()
    {
        parent::__construct();
        $this->setType(Type::SUCCESS);
    }
}