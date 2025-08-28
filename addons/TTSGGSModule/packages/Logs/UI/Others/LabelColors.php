<?php

namespace ModulesGarden\TTSGGSModule\Packages\Logs\UI\Others;

class LabelColors
{
    protected static $colors;

    public function __construct()
    {
        $this->colors = require dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Colors.php';
    }

    public function getConfiguration(string $type)
    {
        return $this->colors[$type];
    }
}
