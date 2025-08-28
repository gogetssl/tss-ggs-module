<?php

namespace ModulesGarden\TTSGGSModule\Core\DataProviders\QueryDataProvider;

class ColumnName
{
    public static function forSelect(string $name): string
    {
        return $name;
    }

    public static function withTableName(string $name): string
    {
        return explode(' as ', $name)[0];
    }

    public static function onlyName(string $name): string
    {
        $exp = explode('.', self::withTableName($name), 2);

        return $exp[1] ?? $exp[0];
    }
}