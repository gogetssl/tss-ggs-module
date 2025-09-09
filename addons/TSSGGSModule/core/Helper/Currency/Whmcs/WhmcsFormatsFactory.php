<?php

namespace ModulesGarden\TSSGGSModule\Core\Helper\Currency\Whmcs;

use ModulesGarden\TSSGGSModule\Core\Helper\Currency\Formatter;
use ModulesGarden\TSSGGSModule\Core\Helper\Currency\Models\Format;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Client;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Currency;
use ModulesGarden\TSSGGSModule\Core\UI\Formatters\Price\Whmcs\WhmcsFormats as WhmcsFormats;

/**
 * @deprecated - use \ModulesGarden\TSSGGSModule\Core\UI\Formatters\Price\Price instead
 */
class WhmcsFormatsFactory
{
    public static function forCurrency(Currency $currency):Formatter
    {
        $formatter = new Formatter();

        $format = WhmcsFormats::getByFormatId($currency->format);

        $formatter->setPrefix($currency->prefix);
        $formatter->setSuffix($currency->suffix);
        $formatter->setFormat(new Format($format->getDecimals(), $format->getDecimalSeparator(), $format->getThousandsSeparator()));

        return $formatter;
    }

    public static function forCurrencyId(int $currencyId):Formatter
    {
        return self::forCurrency(Currency::find($currencyId));
    }

    public static function forClientId(int $clientId):Formatter
    {
        return self::forClient(Client::find($clientId));
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