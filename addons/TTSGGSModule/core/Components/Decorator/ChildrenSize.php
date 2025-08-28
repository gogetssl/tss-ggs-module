<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Decorator;

use ModulesGarden\TTSGGSModule\Core\Components\Enums\DisplayTypes;

class ChildrenSize extends AbstractDecorator
{
    public function fitToParent(): self
    {
        return $this->appendClass(DisplayTypes::FLEX);
    }
}