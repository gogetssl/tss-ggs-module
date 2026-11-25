<?php

namespace TSSGGSModule\App\Libs\Settings;

use TSSGGSModule\App\Kernel\Lang;
use TSSGGSModule\App\Libs\Billing\BalanceMonitor;
use TSSGGSModule\App\Libs\Billing\FinancePolicy;
use TSSGGSModule\App\Libs\Billing\Markups;
use TSSGGSModule\App\Libs\Billing\PricingService;
use TSSGGSModule\App\Libs\Gateway\SSLTrustCenterApi;
use TSSGGSModule\App\Libs\Products\ProductSettings;
use TSSGGSModule\App\Libs\Support\Edition;
use TSSGGSModule\App\Libs\Support\Helpers;

/**
 * What Settings > Finance shows besides its fields: where else prices are decided (with what is set there now), the
 * unit of the amounts, and per vendor account the balance of the last check and the "warn me below" field.
 */
class FinanceScreen
{
    const TEXTS = 'admin.settings.index.forms.finance_settings_form.';

    public static function data(Lang $lang): array
    {
        // not read within the last hour (or never): read now, so the page shows the balance before the first cron run
        BalanceMonitor::run(false, SSLTrustCenterApi::PAGE_TIMEOUT);

        $vendors  = Edition::vendors();
        $accounts = [];
        foreach($vendors as $vendor)
        {
            $info     = BalanceMonitor::info($vendor);
            $currency = (string)($info['currency'] ?? 'USD');
            $account  = self::balanceNow($lang, $vendor, $info) + ['threshold' => null];
            // a monthly invoice account is never stopped by its balance: no amount to set (its saved one is kept)
            if(($info['terms'] ?? '') !== 'invoice')
            {
                $account['threshold'] = [
                    'name'  => BalanceMonitor::thresholdKey($vendor),
                    'label' => $lang->get(self::TEXTS . (count($vendors) > 1 ? 'balanceThresholdBrand' : 'balanceThreshold'), ['brand' => Edition::brandOf($vendor), 'currency' => $currency]),
                    'hint'  => $lang->get(self::TEXTS . 'balanceThreshold_description'),
                ];
            }
            $accounts[] = $account;
        }

        $currency = preg_replace('/[^A-Z]/', '', FinancePolicy::baseCurrencyCode());

        return [
            'rules'        => self::otherPriceRules(),
            // the amount fields show the currency after the number: USD in settings.css, another base currency by a style
            'baseCurrency' => $currency !== '' && $currency !== 'USD' ? $currency : '',
            // the vendor prices are in USD: without a USD currency in WHMCS the module converts with its own rate
            'needsRate'    => !Helpers::getCurrencyIdByCode('USD'),
            'accounts'     => $accounts,
        ];
    }

    /**
     * The other places that decide a price, with what is set there now: the default margin only prices products with
     * automatic pricing, a product margin replaces it, and client markups are added on top at checkout.
     */
    protected static function otherPriceRules(): array
    {
        $products = (new PricingService())->products();
        $auto     = count(array_filter($products, function($p) {
            return $p['auto'];
        }));
        $own = 0;
        foreach($products as $product)
        {
            if(trim((string)ProductSettings::get($product['id'])['margin']) !== '')
            {
                $own++;
            }
        }
        $rules  = Markups::all();
        $global = null;
        foreach($rules as $rule)
        {
            if(!(int)$rule->client_id && !(int)$rule->product_id)
            {
                $global = (float)$rule->markup;
            }
        }

        return [
            ['key' => 'rulesAuto', 'page' => 'pricing', 'link' => 'openPricing', 'auto' => $auto, 'manual' => count($products) - $auto, 'count' => 0, 'global' => ''],
            ['key' => 'rulesProducts', 'page' => 'products', 'link' => 'openProducts', 'auto' => 0, 'manual' => 0, 'count' => $own, 'global' => ''],
            ['key' => !count($rules) ? 'rulesMarkupsNone' : ($global !== null ? 'rulesMarkupsGlobal' : 'rulesMarkups'), 'page' => 'markups', 'link' => 'openMarkups',
             'auto' => 0, 'manual' => 0, 'count' => count($rules), 'global' => rtrim(rtrim(number_format((float)$global, 2, '.', ''), '0'), '.')],
        ];
    }

    /**
     * What the last balance check of a vendor account found, in one line: money left by the account's terms, the
     * check time, a read error or a status that stops orders.
     *
     * @return array html (the texts with escaped values, the account and the amounts bold), tone: warn | ok | ''
     */
    protected static function balanceNow(Lang $lang, string $vendor, array $info): array
    {
        $t    = function($key, array $vars) use ($lang) {
            // the texts themselves hold no markup: they are escaped, then the (escaped, partly bold) values go in
            $text = htmlspecialchars($lang->get(self::TEXTS . $key), ENT_QUOTES, 'UTF-8');
            foreach($vars as $name => $value)
            {
                $text = preg_replace('/:' . preg_quote($name, '/') . '(?![A-Za-z0-9_])/', str_replace(['\\', '$'], ['\\\\', '\\$'], (string)$value), $text);
            }

            return $text;
        };
        $bold = function($value) {
            return '<b>' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '</b>';
        };
        $vars = ['brand' => $bold(Edition::brandOf($vendor))];
        if(empty($info['checked']))
        {
            return ['html' => $t('balanceNotChecked', $vars), 'tone' => ''];
        }

        $currency = (string)($info['currency'] ?? 'USD');
        $vars    += [
            'time'    => htmlspecialchars(date('Y-m-d H:i', (int)$info['checked'])),
            'balance' => $bold(BalanceMonitor::money($info['balance'] ?? 0, $currency)),
            'left'    => $bold(BalanceMonitor::money($info['left'] ?? 0, $currency)),
            'limit'   => $bold(BalanceMonitor::money($info['credit_limit'] ?? 0, $currency)),
            'error'   => htmlspecialchars((string)($info['error'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'status'  => $bold($info['status'] ?? ''),
        ];
        if(!isset($info['balance']))
        {
            return ['html' => $t('balanceError', $vars), 'tone' => 'warn'];
        }

        $terms = (string)($info['terms'] ?? 'prepaid');
        $key   = $terms === 'invoice' ? 'balanceNowInvoice' : ($terms === 'credit' ? ($info['left'] === null ? 'balanceNowCreditNoLimit' : 'balanceNowCredit') : 'balanceNow');
        $text  = $t($key, $vars);
        if(!empty($info['error']))
        {
            $text .= ' ' . $t('balanceErrorLast', $vars);
        }
        $inactive = ($info['status'] ?? null) !== null && $info['status'] !== 'active';
        if($inactive)
        {
            $text .= ' ' . $t('balanceInactive', $vars);
        }
        // when it was read: a second, quieter line
        $text .= '<span class="tssggs-renewal-check__meta">' . $t('balanceUpdated', $vars) . '</span>';

        if($inactive || !empty($info['low']) || !empty($info['error']))
        {
            return ['html' => $text, 'tone' => 'warn'];
        }

        // a credit line that is not running low: green, the reseller can order on credit
        return ['html' => $text, 'tone' => $key === 'balanceNowCredit' ? 'ok' : ''];
    }
}
