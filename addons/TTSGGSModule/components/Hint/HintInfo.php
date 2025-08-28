<?php

namespace ModulesGarden\TTSGGSModule\Components\Hint;

class HintInfo extends Hint
{
    public function __construct()
    {
        $this->setType(self::TYPE_INFO);
        parent::__construct();
    }
}