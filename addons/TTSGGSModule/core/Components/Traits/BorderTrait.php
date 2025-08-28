<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Traits;

use ModulesGarden\TTSGGSModule\Core\Components\Enums\Border;
use ModulesGarden\TTSGGSModule\Core\Components\Enums\BorderColors;
use ModulesGarden\TTSGGSModule\Core\Components\Enums\BorderStyles;
use ModulesGarden\TTSGGSModule\Core\Components\Enums\BorderWidths;
use ReflectionClass;

trait BorderTrait
{
    protected string $style = '';

    /**
     * @param string $color
     * @param string $width
     * @param string $style
     * @return self
     */
    public function setBorder(string $color = BorderColors::DEFAULT, string $width = BorderWidths::WIDTH_1, string $style = BorderStyles::SOLID): self
    {
        $this->appendCss($color);
        $this->appendCss($width);
        $this->setBorderStyle($style);

        return $this;
    }

    /**
     * @param string $color
     * @param string $width
     * @param string $style
     * @return self
     */
    public function setBorderTop(string $color = BorderColors::DEFAULT, string $width = BorderWidths::WIDTH_1, string $style = BorderStyles::SOLID): self
    {
        $this->setBorderColor('top', $color);
        $this->setBorderWidth('top', $width);
        $this->setBorderStyle($style);

        return $this;
    }

    /**
     * @param string $color
     * @param string $width
     * @param string $style
     * @return self
     */
    public function setBorderBottom(string $color = BorderColors::DEFAULT, string $width = BorderWidths::WIDTH_1, string $style = BorderStyles::SOLID): self
    {
        $this->setBorderColor('bottom', $color);
        $this->setBorderWidth('bottom', $width);
        $this->setBorderStyle($style);

        return $this;
    }

    /**
     * @param string $color
     * @param string $width
     * @param string $style
     * @return self
     */
    public function setBorderRight(string $color = BorderColors::DEFAULT, string $width = BorderWidths::WIDTH_1, string $style = BorderStyles::SOLID): self
    {
        $this->setBorderColor('right', $color);
        $this->setBorderWidth('right', $width);
        $this->setBorderStyle($style);

        return $this;
    }

    /**
     * @param string $color
     * @param string $width
     * @param string $style
     * @return self
     */
    public function setBorderLeft(string $color = BorderColors::DEFAULT, string $width = BorderWidths::WIDTH_1, string $style = BorderStyles::SOLID): self
    {
        $this->setBorderColor('left', $color);
        $this->setBorderWidth('left', $width);
        $this->setBorderStyle($style);

        return $this;
    }

    /**
     * @return self
     */
    public function setBorderCircled(): self
    {
        $this->appendCss(Border::CIRCLED);

        return $this;
    }

    protected function addSideToClass(string $side, string $class):string
    {
        return str_replace('-border' , '-border-' . $side, $class);
    }

    protected function setBorderColor(string $side, string $color)
    {
        if (in_array($color, (new ReflectionClass(BorderColors::class))->getConstants())) {
            $this->appendCss($this->addSideToClass($side, $color));
        } else {
            $color = '#' . strtoupper(trim($color, '#'));
            $this->pushToSlot('styles', "{$color} !important", "border-{$side}-color");
        }
    }

    protected function setBorderWidth(string $side, string $width)
    {
        $this->appendCss($this->addSideToClass($side, $width));
    }

    protected function setBorderStyle(string $style)
    {
        if (empty($this->style))
        {
            $this->style = $style;
            $this->appendCss($style);
        }
    }
}