<?php

namespace ModulesGarden\TTSGGSModule\Components\Hint;

class HintDanger extends Hint
{
    public function __construct()
    {
        $this->setType(self::TYPE_DANGER);
        parent::__construct();
    }
}