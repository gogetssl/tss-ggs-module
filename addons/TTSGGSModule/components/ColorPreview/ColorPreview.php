<?php

namespace ModulesGarden\TTSGGSModule\Components\ColorPreview;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;

class ColorPreview extends AbstractComponent
{
    public const COMPONENT = 'ColorPreview';

    public function setRgb(string $color): self
    {
        $color = '#'.trim($color, '#');
        $color = strtoupper($color);

        if ($this->isValidColorHex($color)) {
            $this->setSlot('color', $color);
        }

        return $this;
    }

    protected function isValidColorHex($colorHex)
    {
        return (bool) preg_match("/^#[0-9A-F]{6}$/i", $colorHex);
    }

    public function setSquareShape(): self
    {
        $this->setSlot('squareShape', true);
        return $this;
    }
}