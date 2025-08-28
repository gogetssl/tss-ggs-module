<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Decorator;

use ModulesGarden\TTSGGSModule\Core\Components\Enums\FontStyles;
use ModulesGarden\TTSGGSModule\Core\Components\Enums\FontTransforms;
use ModulesGarden\TTSGGSModule\Core\Components\Enums\FontWeights;

class Font extends AbstractDecorator
{
    public function setLightWeight(): self
    {
        $this->component->appendCss(FontWeights::LIGHT);

        return $this;
    }

    public function setBoldWeight(): self
    {
        $this->component->appendCss(FontWeights::BOLD);

        return $this;
    }

    public function setItalicStyle(): self
    {
        $this->component->appendCss(FontStyles::ITALIC);

        return $this;
    }

    public function setNormalTransform(): self
    {
        $this->component->appendCss(FontTransforms::NORMAL);

        return $this;
    }

    public function setLowercaseTransform(): self
    {
        $this->component->appendCss(FontTransforms::LOWERCASE);

        return $this;
    }

    public function setUppercaseTransform(): self
    {
        $this->component->appendCss(FontTransforms::UPPERCASE);

        return $this;
    }

    public function setCapitalizeTransform(): self
    {
        $this->component->appendCss(FontTransforms::CAPITALIZE);

        return $this;
    }

    public function setColor(string $colorClass): self
    {
        $this->component->appendCss($colorClass);

        return $this;
    }

}