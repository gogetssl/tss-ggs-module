<?php

namespace ModulesGarden\TTSGGSModule\Core\Helper\Converter;

class ArrayFormat
{
    /**
     * Change square brackets to dots
     * e.g. filters[0][fieldType] -> filters.0.fieldType
     *
     * @param  string  $name
     * @return string
     */
    public static function parseKeyToDotedFormat(string $name): string
    {
        return str_replace(['[', ']'], ['.', ''], $name);
    }
}