<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use WHMCS\Database\Capsule;

/**
 * How a selling price is made from the reseller cost (Settings > Finance). One place, used by the automatic price
 * update, the Pricing page ("apply margin", below-cost raise), the product import and new SAN options:
 *
 *   cost (gateway, USD)  x exchange rate                         -> cost in the base currency (USD, or the default currency)
 *   + margin %, lowered by "per extra year" for 2 and 3 year terms (never below 0)
 *   at least cost + minimum profit (the product itself, not the SAN options)
 *   converted to each WHMCS currency, + currency buffer % for currencies other than USD
 *   rounded up (.99 / .95 / whole / half) - rounding never lowers a price
 */
class FinancePolicy
{
    const ROUNDING      = ['none', '99', '95', 'whole', 'half'];
    const RATE_SOURCES  = ['manual', 'ecb'];
    const RENEWAL_MODES = ['never', 'belowCost', 'always'];
    const BELOW_COST    = ['notify', 'raise'];

    const ECB_FEED      = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
    const BALANCE_ALERT_EVERY = 21600; // 6 hours between two "balance too low" e-mails

    protected static $baseCurrency;
    protected static $codes = [];

    // ---- settings ---- //

    public static function minProfit(): float
    {
        return max(0, (float)GeneralSettings::get('minProfit'));
    }

    public static function termStep(): float
    {
        return max(0, (float)GeneralSettings::get('termMarginStep'));
    }

    public static function rounding(): string
    {
        $mode = (string)GeneralSettings::get('priceRounding');

        return in_array($mode, self::ROUNDING, true) ? $mode : 'none';
    }

    public static function buffer(): float
    {
        return max(0, (float)GeneralSettings::get('currencyBuffer'));
    }

    public static function renewalMode(): string
    {
        $mode = (string)GeneralSettings::get('renewalPriceMode');

        return in_array($mode, self::RENEWAL_MODES, true) ? $mode : 'never';
    }

    public static function raiseBelowCost(): bool
    {
        return GeneralSettings::get('belowCostAction') === 'raise';
    }

    /** USD when WHMCS has it, otherwise the default currency: the currency the exchange rate converts the gateway cost into */
    public static function baseCurrencyId(): int
    {
        if(self::$baseCurrency === null)
        {
            self::$baseCurrency = (int)Capsule::table('tblcurrencies')->where('code', 'USD')->value('id')
                ?: (int)Capsule::table('tblcurrencies')->where('default', 1)->value('id');
        }

        return self::$baseCurrency;
    }

    public static function baseCurrencyCode(): string
    {
        return self::currencyCode(self::baseCurrencyId()) ?: 'USD';
    }

    protected static function currencyCode($currencyId): string
    {
        $currencyId = (int)$currencyId;
        if(!array_key_exists($currencyId, self::$codes))
        {
            self::$codes[$currencyId] = strtoupper((string)Capsule::table('tblcurrencies')->where('id', $currencyId)->value('code'));
        }

        return self::$codes[$currencyId];
    }

    // ---- the price ---- //

    /** Margin for a term: the extra years lower it by the "per extra year" step, never below 0 */
    public static function termMargin($margin, $months): float
    {
        $years = max(1, (int)ceil((int)$months / 12));

        return max(0, (float)$margin - self::termStep() * ($years - 1));
    }

    /**
     * Price in the base currency from a cost in the base currency
     * @param bool $product the certificate itself (minimum profit applies) - false for a SAN option
     */
    public static function basePrice($cost, $margin, $months, $product = true): float
    {
        $cost  = (float)$cost;
        $price = $cost * (1 + self::termMargin($margin, $months) / 100);
        if($product && $cost > 0)
        {
            $price = max($price, $cost + self::minProfit());
        }

        return $price;
    }

    /** A base currency amount in a WHMCS currency, with the currency buffer for currencies other than USD */
    public static function toCurrency($amount, $currencyId): float
    {
        $baseId = self::baseCurrencyId();
        $value  = (int)$currencyId === $baseId ? (float)$amount : (float)\convertCurrency((float)$amount, $baseId, (int)$currencyId);
        if(self::currencyCode($currencyId) !== 'USD')
        {
            $value *= 1 + self::buffer() / 100;
        }

        return $value;
    }

    /** Rounds a final price up to the chosen ending; 0 stays 0 */
    public static function round($amount): float
    {
        $amount = round((float)$amount, 2);
        if($amount <= 0)
        {
            return $amount;
        }

        switch(self::rounding())
        {
            case '99':
                return round(ceil($amount + 0.01) - 0.01, 2);
            case '95':
                return round(ceil($amount + 0.05) - 0.05, 2);
            case 'whole':
                return (float)ceil($amount - 0.0001);
            case 'half':
                return ceil($amount * 2 - 0.0001) / 2;
            default:
                return $amount;
        }
    }

    /**
     * Selling price in a WHMCS currency
     * @param float $baseCost cost already in the base currency (gateway cost x exchange rate)
     */
    public static function sell($baseCost, $margin, $months, $currencyId, $product = true): float
    {
        return self::round(self::toCurrency(self::basePrice($baseCost, $margin, $months, $product), $currencyId));
    }

    // ---- exchange rate ---- //

    /**
     * "Daily rate (European Central Bank)": refreshes the USD -> base currency rate when WHMCS has no USD currency.
     * Returns the rate in use (the stored one when the feed can not be read).
     */
    public static function refreshRate(): float
    {
        $configuration = (new AddonModuleRepository())->getModuleConfiguration();
        $finance       = isset($configuration['financeSettings']) && is_array($configuration['financeSettings']) ? $configuration['financeSettings'] : [];
        $stored        = (float)($finance['rate'] ?? 1) ?: 1;

        if(GeneralSettings::get('rateSource') !== 'ecb' || Capsule::table('tblcurrencies')->where('code', 'USD')->exists())
        {
            return $stored;
        }

        $target = self::baseCurrencyCode();
        $rates  = self::ecbRates();
        if(!$rates || !isset($rates['USD']) || ($target !== 'EUR' && !isset($rates[$target])))
        {
            GeneralSettings::activity('Exchange rate: the daily rate USD -> ' . $target . ' could not be read, the last rate (' . $stored . ') stays in use');

            return $stored;
        }

        // the feed is EUR based: 1 EUR = x USD, 1 EUR = y target -> 1 USD = y / x target
        $rate = round(($target === 'EUR' ? 1 : $rates[$target]) / $rates['USD'], 6);
        if(abs($rate - $stored) > 0.000001)
        {
            $finance['rate']        = $rate;
            $finance['rateUpdated'] = date('Y-m-d H:i');
            (new AddonModuleRepository())->saveFinanceSettings($finance);
        }

        return $rate;
    }

    /** ['USD' => 1.08, ...] per 1 EUR, [] when the feed is not available */
    protected static function ecbRates(): array
    {
        $ch = curl_init(self::ECB_FEED);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_TIMEOUT => 20]);
        $xml  = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if(!$xml || $code !== 200)
        {
            return [];
        }

        preg_match_all("/currency=['\"]([A-Z]{3})['\"]\s+rate=['\"]([0-9.]+)['\"]/", (string)$xml, $matches, PREG_SET_ORDER);
        $rates = [];
        foreach($matches as $match)
        {
            $rates[$match[1]] = (float)$match[2];
        }

        return $rates;
    }

    // ---- reseller balance ---- //

    /** Gateway answers that mean the reseller account has not enough funds for the order */
    public static function isBalanceError($message): bool
    {
        return (bool)preg_match('/insufficient|not enough (funds|balance|money|credit)|low balance|balance is too low|top up|add funds/i', (string)$message);
    }

    /** E-mails the administrators that an order failed for lack of funds (at most every 6 hours) */
    public static function balanceAlert($message, $serviceId = null)
    {
        if(!GeneralSettings::enabled('balanceAlert') || time() - (int)GeneralSettings::get('balanceAlertSent') < self::BALANCE_ALERT_EVERY)
        {
            return;
        }
        GeneralSettings::save(['balanceAlertSent' => time()]);

        $text = '<p>The certificate gateway refused an order' . ($serviceId ? ' (service #' . (int)$serviceId . ')' : '')
            . ' because your reseller balance is too low:</p><p><i>' . htmlspecialchars((string)$message) . '</i></p>'
            . '<p>Top up your reseller account. Paid orders are not sent to the certificate authority until the balance covers them: '
            . 'open each failed service and run "Create" again (or let the client retry) once the balance is topped up.</p>';

        $result = localAPI('SendAdminEmail', ['customsubject' => Edition::name() . ': reseller balance too low - orders are failing', 'custommessage' => $text, 'type' => 'system']);
        if(($result['result'] ?? '') !== 'success')
        {
            GeneralSettings::activity('Balance alert e-mail to the administrators failed: ' . ($result['message'] ?? 'unknown error'));
        }
    }
}
