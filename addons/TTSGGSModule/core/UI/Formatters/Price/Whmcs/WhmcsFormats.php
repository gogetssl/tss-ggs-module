<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Formatters\Price\Whmcs;

use ModulesGarden\TTSGGSModule\Core\UI\Formatters\Price\Models\Format;

class WhmcsFormats
{
    public static function getByFormatId(int $formatId):Format
    {
        return match ($formatId)
        {
            2 => new Format(2, ".", ","),
            3 => new Format(2, ",", "."),
            4 => new Format(0, "", ","),
            default => self::getDefaultFormat(),
        };
    }

    public static function getDefaultFormat():Format
    {
        return new Format(2, ".", "");
    }
}