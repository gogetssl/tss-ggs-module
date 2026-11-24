<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms;


use ModulesGarden\TSSGGSModule\App\Libs\BalanceMonitor;
use ModulesGarden\TSSGGSModule\App\Libs\Edition;
use ModulesGarden\TSSGGSModule\App\Libs\FinancePolicy;
use ModulesGarden\TSSGGSModule\App\Libs\Markups;
use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\Libs\PricingService;
use ModulesGarden\TSSGGSModule\App\Libs\ProductSettings;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Concerns\SettingsLayout;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers\FinanceSettingsProvider;
use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TSSGGSModule\Components\Number\Number;
use ModulesGarden\TSSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TSSGGSModule\Components\TextArea\TextArea;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

/**
 * Settings > Finance: how prices are made from the reseller cost (App\Libs\FinancePolicy), what happens when a price
 * falls below cost, renewal prices of existing services, currency, billing rules and the reseller balance alert.
 * The margin and the exchange rate are stored in "financeSettings", the rest in the general settings.
 */
class FinanceSettingsForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    use SettingsLayout;

    protected $configuration;

    public function __construct($configuration = false)
    {
        parent::__construct();

        $this->configuration  = $configuration;
        $this->provider       = FinanceSettingsProvider::class;
        $this->providerAction = FinanceSettingsProvider::ACTION_UPDATE;
    }

    public function loadHtml(): void
    {
        $this->builder = BuilderCreator::oneColumn($this);
        $this->setId('FinanceSettingsForm');

        $pricing = $this->section('pricing', 'trending-up');
        $pricing->addElement((new Text())->setText($this->otherPriceRules()));
        $this->builder->addFieldInContainer($pricing, (new Number())->setName('profitMargin')->addClass('tssggs-percent')->setMin(0)->setMax(1000)->required()->numeric()->between(0, 1000), true);
        $currency = preg_replace('/[^A-Z]/', '', FinancePolicy::baseCurrencyCode());
        if($currency !== '' && $currency !== 'USD')
        {
            // the amount field shows the currency after the number: USD in settings.css, another base currency here
            $pricing->addElement((new Text())->setText('<style>#layers2 .tssggs-settings form .tssggs-sec .lu-form-group:has(> .tssggs-money)::after { content: "'
                . $currency . '"; }</style>'));
        }
        $this->builder->addFieldInContainer($pricing, (new FormInputText())->setName('minProfit')->addClass('tssggs-money')->numeric()->between(0, 100000), true);
        $this->builder->addFieldInContainer($pricing, (new Number())->setName('termMarginStep')->addClass('tssggs-percent')->setMin(0)->setMax(100)->numeric()->between(0, 100), true);
        $this->builder->addFieldInContainer($pricing, (new Dropdown())->setName('priceRounding'), true);
        $this->builder->addFieldInContainer($pricing, (new Switcher())->setName('keepMarginOnSync')->addClass('switcher-revert'), true);

        $belowCost = $this->section('belowCost', 'alert-circle-outline');
        $this->builder->addFieldInContainer($belowCost, (new Dropdown())->setName('belowCostAction'), true);
        $this->builder->addFieldInContainer($belowCost, (new Switcher())->setName('belowCostEmail')->addClass('switcher-revert'), true);

        $renewals = $this->section('renewalPrices', 'refresh', null, ['renewalPoint_never', 'renewalPoint_belowCost', 'renewalPoint_always']);
        $this->builder->addFieldInContainer($renewals, (new Dropdown())->setName('renewalPriceMode'), true);
        $this->builder->addFieldInContainer($renewals, (new Switcher())->setName('renewalPriceClientEmail')->addClass('switcher-revert'), true);
        $this->builder->addFieldInContainer($renewals, (new TextArea())->setName('renewalPriceEmailText'), true);

        $currency = $this->section('currency', 'currency-usd');
        if(!Helpers::getCurrencyIdByCode('USD')) // the gateway prices are in USD: without a USD currency the module converts with its own rate
        {
            $this->builder->addFieldInContainer($currency, (new Dropdown())->setName('rateSource'), true);
            $this->builder->addFieldInContainer($currency, (new FormInputText())->setName('rate')->required()->numeric(), true);
        }
        $this->builder->addFieldInContainer($currency, (new FormInputText())->setName('currencyBuffer')->addClass('tssggs-percent')->numeric()->between(0, 50), true);

        // no fields: unpaid certificates are terminated by WHMCS's own automation, the description says where
        $this->section('billing', 'credit-card-clock-outline');

        $balance = $this->section('balance', 'hand-coin-outline');
        $this->builder->addFieldInContainer($balance, (new Switcher())->setName('balanceAlert')->addClass('switcher-revert'), true);
        // not read within the last hour (or never): read now, so the page shows the balance before the first cron run
        BalanceMonitor::run();
        $vendors = Edition::vendors();
        foreach($vendors as $vendor)
        {
            $info     = BalanceMonitor::info($vendor);
            $brand    = Edition::brandOf($vendor);
            $currency = (string)($info['currency'] ?? 'USD');
            $balance->addElement((new Text())->setText($this->balanceNow($vendor, $info)));
            // a monthly invoice account is never stopped by its balance: no amount to set (its saved one is kept)
            if(($info['terms'] ?? '') === 'invoice')
            {
                continue;
            }
            $title    = count($vendors) > 1 ? 'balanceThresholdBrand' : 'balanceThreshold';
            $this->builder->addFieldInContainer($balance, (new FormInputText())->setName(BalanceMonitor::thresholdKey($vendor))
                ->setTitle($this->translate($title, [':brand' => $brand, ':currency' => $currency]))
                ->setDescription($this->translate('balanceThreshold_description'))
                ->numeric()->between(0, 10000000), true);
        }

        $this->saveBar();
    }

    /**
     * What the last balance check of a vendor account found, in one line: money left by the account's terms, the
     * check time, a read error or a status that stops orders
     */
    protected function balanceNow($vendor, array $info): string
    {
        // the values are escaped here (the error and the status come from the API), the account and the amounts bold;
        // the language texts themselves hold no markup
        $bold = function($value) { return '<b>' . htmlspecialchars((string)$value) . '</b>'; };
        $vars = [':brand' => $bold(Edition::brandOf($vendor))];
        if(empty($info['checked']))
        {
            return $this->balanceBox($this->translate('balanceNotChecked', $vars), false);
        }
        $currency = (string)($info['currency'] ?? 'USD');
        $vars    += [
            ':time'    => htmlspecialchars(date('Y-m-d H:i', (int)$info['checked'])),
            ':balance' => $bold(BalanceMonitor::money($info['balance'] ?? 0, $currency)),
            ':left'    => $bold(BalanceMonitor::money($info['left'] ?? 0, $currency)),
            ':limit'   => $bold(BalanceMonitor::money($info['credit_limit'] ?? 0, $currency)),
            ':error'   => htmlspecialchars((string)($info['error'] ?? '')),
            ':status'  => $bold($info['status'] ?? ''),
        ];
        if(!isset($info['balance']))
        {
            return $this->balanceBox($this->translate('balanceError', $vars), true);
        }
        $terms = (string)($info['terms'] ?? 'prepaid');
        $key   = $terms === 'invoice' ? 'balanceNowInvoice' : ($terms === 'credit' ? ($info['left'] === null ? 'balanceNowCreditNoLimit' : 'balanceNowCredit') : 'balanceNow');
        $text  = $this->translate($key, $vars);
        if(!empty($info['error']))
        {
            $text .= ' ' . $this->translate('balanceErrorLast', $vars);
        }
        $inactive = ($info['status'] ?? null) !== null && $info['status'] !== 'active';
        if($inactive)
        {
            $text .= ' ' . $this->translate('balanceInactive', $vars);
        }
        // when it was read: a second, quieter line
        $text .= '<span class="tssggs-renewal-check__meta">' . $this->translate('balanceUpdated', $vars) . '</span>';

        if($inactive || !empty($info['low']) || !empty($info['error']))
        {
            return $this->balanceBox($text, true);
        }

        // a credit line that is not running low: green, the reseller can order on credit
        return $this->balanceBox($text, false, $key === 'balanceNowCredit');
    }

    /** $html: a language text with escaped values (balanceNow) */
    protected function balanceBox(string $html, bool $warn, bool $ok = false): string
    {
        $class = $warn ? 'tssggs-renewal-check__warning tssggs-renewal-check__warning--amber'
            : ($ok ? 'tssggs-renewal-check__warning tssggs-renewal-check__warning--green' : 'tssggs-renewal-check__summary');

        return '<div class="tssggs-renewal-check"><p class="' . $class . '">' . $html . '</p></div>';
    }

    /**
     * The other places that decide a price, with what is set there now: the default margin here only prices products
     * with automatic pricing, a product margin replaces it, and client markups are added on top at checkout.
     */
    protected function otherPriceRules(): string
    {
        $products = (new PricingService())->products();
        $auto     = count(array_filter($products, function($p) { return $p['auto']; }));
        $own      = 0;
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

        $url  = function($page) {
            return 'addonmodules.php?module=TSSGGSModule&mg-page=' . $page;
        };
        $item = function($key, array $vars, $page, $link) use ($url) {
            return '<li><b>' . htmlspecialchars($this->translate($key)) . '</b> ' . htmlspecialchars($this->translate($key . '_text', $vars))
                . ' <a href="' . htmlspecialchars($url($page)) . '">' . htmlspecialchars($this->translate($link)) . '</a></li>';
        };

        $markupKey = !count($rules) ? 'rulesMarkupsNone' : ($global !== null ? 'rulesMarkupsGlobal' : 'rulesMarkups');

        return '<div class="tssggs-sec__desc tssggs-sec__desc--rules"><p>' . htmlspecialchars($this->translate('rulesIntro')) . '</p><ul>'
            . $item('rulesAuto', [':auto' => $auto, ':manual' => count($products) - $auto], 'pricing', 'openPricing')
            . $item('rulesProducts', [':count' => $own], 'products', 'openProducts')
            . $item($markupKey, [':count' => count($rules), ':global' => rtrim(rtrim(number_format((float)$global, 2, '.', ''), '0'), '.')], 'markups', 'openMarkups')
            . '</ul></div>';
    }
}
