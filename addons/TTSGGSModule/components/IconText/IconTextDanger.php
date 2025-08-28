<?php

namespace ModulesGarden\TTSGGSModule\Components\IconText;

use ModulesGarden\TTSGGSModule\Core\Components\Enums\Type;

class IconTextDanger extends IconText
{
    public function __construct()
    {
        parent::__construct();
        $this->setType(Type::DANGER);
    }
}