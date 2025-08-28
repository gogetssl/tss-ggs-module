<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Formatters\Price\Builders;

use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Client;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Currency;
use ModulesGarden\TTSGGSModule\Core\UI\Formatters\Price\Formatter;
use ModulesGarden\TTSGGSModule\Core\UI\Formatters\Price\Whmcs\WhmcsFormats;

class FormatterBuilder
{
    protected static array $currencies = [];
    protected static array $clients = [];

    public static function forCurrency(Currency $currency):Formatter
    {
        $formatter = new Formatter();

        $formatter->setPrefix($currency->prefix);
        $formatter->setSuffix($currency->suffix);
        $formatter->setFormat(WhmcsFormats::getByFormatId($currency->format));

        return $formatter;
    }

    public static function forCurrencyId(int $currencyId):Formatter
    {
        if (isset(static::$currencies[$currencyId]))
        {
            return static::$currencies[$currencyId];
        }

        $currency = Currency::find($currencyId);

        static::$currencies[$currencyId] = $currency;

        return self::forCurrency($currency);
    }

    public static function forClientId(int $clientId):Formatter
    {
        if (isset(static::$clients[$clientId]))
        {
            return static::$clients[$clientId];
        }

        $client = Client::find($clientId);

        static::$clients[$clientId] = $client;

        return self::forClient($client);
    }

    public static function forClient(Client $client):Formatter
    {
        return self::forCurrency($client->currencyObj);
    }

    public static function forCurrentClient():Formatter
    {
        return self::forCurrency(Currency::factoryForClientArea());
    }

    public static function whmcsDefault():Formatter
    {
        return self::forCurrency(Currency::defaultCurrency()->first());
    }
}