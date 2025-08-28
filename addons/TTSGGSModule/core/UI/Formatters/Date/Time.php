<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Formatters\Date;

class Time extends BaseDate
{
    protected static string $moduleFormatSetting  = "configuration.formatters.time";

    public function getDefaultFormat(): string
    {
        return self::DEFAULT_TIME_FORMAT;
    }
}