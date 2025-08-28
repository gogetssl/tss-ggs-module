<?php

namespace ModulesGarden\TTSGGSModule\Core\Helper\Color\Models;

class RgbaColor extends Color
{
    protected int $r;
    protected int $g;
    protected int $b;

    public function __construct(int $r = 255, int $g = 255, int $b = 255, float $a = 1)
    {
        $this->r        = $this->checkColorValue($r);
        $this->g        = $this->checkColorValue($g);
        $this->b        = $this->checkColorValue($b);
        $this->opacity  = $this->checkOpacityValue($a);
    }

    public function getHex(string $prefix = "#"): string
    {
        return $prefix . strtoupper(dechex($this->r) . dechex($this->g) . dechex($this->b));
    }

    public function getHexWithOpacity(string $prefix = "#"): string
    {
        return $this->getHex($prefix) . strtoupper($this->opacityToHex($this->opacity));
    }

    public function getRgb(): string
    {
        return sprintf("rgb(%d, %d, %d)", $this->r, $this->g, $this->b);
    }

    public function getRgba(): string
    {
        return sprintf("rgba(%d, %d, %d, %0.2f)", $this->r, $this->g, $this->b, $this->opacity);
    }

    protected function checkColorValue(int $color): int
    {
        if ($color > 255)
        {
            throw new \Exception("Incorrect color value provided. Color should not be greater than 1.");
        }

        if ($color < 0)
        {
            throw new \Exception("Incorrect color value provided. Color must be a positive value.");
        }

        return $color;
    }

}