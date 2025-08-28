<?php

namespace ModulesGarden\TTSGGSModule\Core\Helper\Color\Models;

abstract class Color
{
    abstract public function getHex():string;
    abstract public function getHexWithOpacity():string;
    abstract public function getRgb():string;
    abstract public function getRgba():string;

    //TODO implement formats:
    //abstract public function getHsl()
    //abstract public function getHsla()
    //abstract public function getOklch()

    //TODO ADD predefined color:
    //ex.: public static function red():self

    protected float $opacity;

    public static function transparent():self
    {
        $color = new static();
        $color->opacity = 0;
        return $color;
    }

    public function setOpacity(float $opacity)
    {
        $this->opacity = $this->checkOpacityValue($opacity);
    }

    protected function checkOpacityValue(float $opacity): float
    {
        if ($opacity > 1)
        {
            throw new \Exception("Incorrect opacity value provided. Opacity should not be greater than 1.");
        }

        if ($opacity < 0)
        {
            throw new \Exception("Incorrect opacity value provided. Opacity must be a positive value.");
        }

        return $opacity;
    }

    protected function opacityToHex(float $opacity): string
    {
        return dechex(floor($opacity * 255));
    }
}