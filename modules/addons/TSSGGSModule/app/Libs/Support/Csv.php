<?php

namespace TSSGGSModule\App\Libs\Support;

/**
 * Cells of the CSV files the module offers for download.
 *
 * A spreadsheet runs a cell that starts with = + - @ (or a tab / line break before one) as a formula. Names, domains
 * and vendor messages come from outside, so such a cell is written with a leading apostrophe: the spreadsheet shows
 * it as text. Numbers keep their sign.
 */
class Csv
{
    public static function cell($value)
    {
        if(is_int($value) || is_float($value) || $value === null || is_bool($value))
        {
            return $value;
        }
        $value = (string)$value;
        if($value === '' || is_numeric($value))
        {
            return $value;
        }

        return preg_match('/^[\s]*[=+\-@]/', $value) || preg_match('/^[\t\r\n]/', $value) ? "'" . $value : $value;
    }

    /** @return array the cells of a row, each made safe */
    public static function row(array $cells): array
    {
        return array_map([self::class, 'cell'], $cells);
    }
}
