<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Decorator;

use ModulesGarden\TTSGGSModule\Core\Components\Enums\BackgroundColor;

class Background extends AbstractDecorator
{
    public function setGrey(): self
    {
        return $this->setColor(BackgroundColor::GREY);
    }

    public function setColor(string $colorClass): self
    {
        $this->component->appendCss($colorClass);

        return $this;
    }

    public function setDefault(): self
    {
        return $this->setColor(BackgroundColor::DEFAULT);
    }
}