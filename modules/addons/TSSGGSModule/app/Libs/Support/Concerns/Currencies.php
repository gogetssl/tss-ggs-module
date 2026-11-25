<?php

namespace TSSGGSModule\App\Libs\Support\Concerns;

use TSSGGSModule\App\Libs\Support\Helpers;
use TSSGGSModule\App\Models\Whmcs\Client;
use TSSGGSModule\App\Models\Whmcs\Currency;

/**
 * Currencies: the default currency, conversion, formatting, tax.
 * Part of Helpers (used there as a trait).
 */
trait Currencies
{
    public static function getDefaultCurrency()
    {
        return Currency::where('default', "1")->first();
    }

    public static function clientCurrencyToDefaultCurrency($amount, $clientId)
    {
        $amount          = floatval($amount);
        $defaultCurrency = Helpers::getDefaultCurrency();

        $clientCurrency = Client::findOrFail($clientId)->currency;

        return \convertCurrency($amount, $clientCurrency->id, $defaultCurrency->id);
    }

    public static function formatDefaultCurrency($amount)
    {
        $defaultCurrency = Helpers::getDefaultCurrency();

        return (string)\formatCurrency($amount, $defaultCurrency->id);
    }

    public static function getCurrencySymbol($currencyId = false)
    {
        if($currencyId)
        {
            $currency = Currency::findOrFail($currencyId);
        }
        else
        {
            $currency = Helpers::getDefaultCurrency();
        }

        return $currency->prefix ?: $currency->code;
    }

    public static function getCurrencyIdByCode($currencyCode)
    {
        $currency = Currency::where('code', $currencyCode)->first();

        return (int)$currency->id;
    }

    public static function getDefaultCurrencyId()
    {
        $currency = Currency::where('default', 1)->first();

        return (int)$currency->id;
    }

    public static function getDefaultCurrencyCode()
    {
        $currency = Currency::where('default', 1)->first();

        return $currency->code;
    }

    public static function getTaxedValue($value, $taxRate, $taxRate2)
    {
        global $CONFIG;

        if($CONFIG["TaxType"] == "Exclusive")
        {
            if($CONFIG["TaxL2Compound"])
            {
                $value = $value + $value * $taxRate / 100;
                $value = $value + $value * $taxRate2 / 100;
            }
            else
            {
                $value = $value + $value * $taxRate / 100 + $value * $taxRate2 / 100;
            }
        }

        return $value;
    }
}
