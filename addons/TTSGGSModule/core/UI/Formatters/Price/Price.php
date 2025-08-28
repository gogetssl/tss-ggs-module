<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Formatters\Price;

use ModulesGarden\TTSGGSModule\Core\UI\Formatters\Price\Builders\FormatterBuilder;
use ModulesGarden\TTSGGSModule\Core\UI\Formatters\Price\Models\Format;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Client;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Currency;

class Price
{
    public static function format(float $price, ?string $prefix = null, ?string $suffix  = null, ?Format $format  = null):string
    {
        return (new Formatter($prefix, $suffix, $format))->format($price);
    }

    public static function formatForCurrency(float $price, Currency $currency):string
    {
        return FormatterBuilder::forCurrency($currency)->format($price);
    }

    public static function formatForCurrencyId(float $price, int $currencyId):string
    {
        return FormatterBuilder::forCurrencyId($currencyId)->format($price);
    }

    public static function formatForClient(float $price, Client $client):string
    {
        return FormatterBuilder::forClient($client)->format($price);
    }

    public static function formatForClientId(float $price, int $clientId):string
    {
        return FormatterBuilder::forClientId($clientId)->format($price);
    }

    public static function forCurrentClient(float $price):string
    {
        return FormatterBuilder::forCurrentClient()->format($price);
    }

    public static function whmcsDefault(float $price):string
    {
        return FormatterBuilder::whmcsDefault()->format($price);
    }
}