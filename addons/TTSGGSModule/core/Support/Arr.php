<?php

namespace ModulesGarden\TTSGGSModule\Core\Support;

use mysql_xdevapi\Statement;

/**
 * @method static set(array $array, string $name, $value)
 * @method static get(array $array, string $name, $default = null)
 * @method static forget(array $array, string $name)
 * @method static exists(array $array, string $name)
 * @method static dot(array $array)
 * More information here: https://laravel.com/docs/9.x/helpers
 */
class Arr extends \Illuminate\Support\Arr
{
    public static function undot($dotedArray)
    {
        $results = [];

        foreach ($dotedArray as $key => $value) {
            Arr::set($results, $key, $value);
        }
        return $results;
    }

    /**
     * Walk baseArray recursive and replace element from secondaryArray if keys matches
     * Replaces elements that are not an array and merge elements that are an array
     * @param array $baseArray
     * @param array $secondaryArray
     * @return array
     */
    public static function mergeRecursiveDistinct(array $baseArray, array $secondaryArray): array
    {
        foreach ($baseArray as $key => &$value)
        {
            if (!array_key_exists($key, $secondaryArray))
            {
                continue;
            }

            $secondArrayElement = $secondaryArray[$key];

            if (is_array($value))
            {
                $value = is_array($secondArrayElement) ?
                    self::mergeRecursiveDistinct($value, $secondArrayElement):
                    $secondArrayElement;

            } else {
                $value = $secondArrayElement;
            }
        }

        return array_merge($secondaryArray, $baseArray);
    }
}
