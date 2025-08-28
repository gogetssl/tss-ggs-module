<?php

namespace ModulesGarden\TTSGGSModule\Core\Helper\Color\Models;

class HexColor extends Color
{
    protected string $hex;

    public function __construct(string $hex = "fff", float $opacity = 1)
    {
        $this->hex      = $this->checkColorHex($hex);
        $this->opacity  = $this->checkOpacityValue($opacity);
    }

    public function getHex(string $prefix = "#"): string
    {
        return $prefix . ltrim(strtoupper($this->hex), $prefix);
    }

    public function getHexWithOpacity(string $prefix = "#"): string
    {
        return $this->getHex($prefix) . strtoupper($this->opacityToHex($this->opacity));
    }

    public function getRgb(): string
    {
        list($r, $g, $b) = array_map('hexdec', str_split($this->hex, 2));

        return sprintf("rgb(%d, %d, %d)", $r, $g, $b);
    }

    public function getRgba(): string
    {
        list($r, $g, $b) = array_map('hexdec', str_split($this->hex, 2));

        return sprintf("rgba(%d, %d, %d, %0.2f)", $r, $g, $b, $this->opacity);
    }

    protected function checkColorHex(string $hex): string
    {
        $hex = trim($hex, "#");

        if (preg_match("/^[a-fA-F0-9]{2,}$/i", $hex) === false)
        {
            throw new \Exception("Incorrect color hex provided.");
        }

        $hexLength = strlen($hex);

        if ($hexLength != 3 && $hexLength != 6)
        {
            throw new \Exception("Incorrect color hex provided. Length of string must be 3 or 6.");
        }

        if (strlen($hex) == 6 )
        {
            return $hex;
        }

        $hexElements = str_split($hex);

        return implode( "", [$hexElements[0], $hexElements[0], $hexElements[1], $hexElements[1], $hexElements[2], $hexElements[2]]);
    }
}