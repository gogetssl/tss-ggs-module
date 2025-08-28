<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Formatters\Date;

class DateTime extends Date
{
    protected static string $moduleFormatSetting  = "configuration.formatters.dateTime";

    protected static function getWhmcsFormat():string
    {
        return parent::getWhmcsFormat() . " " . self::DEFAULT_TIME_FORMAT;
    }
}