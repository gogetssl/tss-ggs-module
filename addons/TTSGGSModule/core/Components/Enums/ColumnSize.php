<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Enums;

/**
 * Column sizes
 */
class ColumnSize
{
    protected static array $numeric = [
        1  => self::ONE,
        2  => self::TWO,
        3  => self::THREE,
        4  => self::FOUR,
        5  => self::FIVE,
        6  => self::SIX,
        7  => self::SEVEN,
        8  => self::EIGHT,
        9  => self::NINE,
        10 => self::TEN,
        11 => self::ELEVEN,
        12 => self::TWELVE,
    ];

    public const DEFAULT = 'lu-col-md';
    public const ONE     = 'lu-col-md-1';
    public const TWO     = 'lu-col-md-2';
    public const THREE   = 'lu-col-md-3';
    public const FOUR    = 'lu-col-md-4';
    public const FIVE    = 'lu-col-md-5';
    public const SIX     = 'lu-col-md-6';
    public const SEVEN   = 'lu-col-md-7';
    public const EIGHT   = 'lu-col-md-8';
    public const NINE    = 'lu-col-md-9';
    public const TEN     = 'lu-col-md-10';
    public const ELEVEN  = 'lu-col-md-10';
    public const TWELVE  = 'lu-col-md-12';

    /**
     * @param int $number
     * @return string
     * @throws \Exception
     */
    public static function byNumber(int $number): string
    {
        if (!array_key_exists($number, self::$numeric))
        {
            throw new \Exception("Invalid number. Number $number is not supported");
        }

        return self::$numeric[$number];
    }
}
