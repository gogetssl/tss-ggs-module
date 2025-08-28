<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Decorator;

class Collapse extends AbstractDecorator
{
    const COLLAPSE_HIDDEN = "lu-collapse";
    const COLLAPSE_SHOWN = "lu-collapse lu-show";

    public function collapsed(): self
    {
        return $this->appendClass(self::COLLAPSE_HIDDEN);
    }

    public function visible(): self
    {
        return $this->appendClass(self::COLLAPSE_SHOWN);
    }
}