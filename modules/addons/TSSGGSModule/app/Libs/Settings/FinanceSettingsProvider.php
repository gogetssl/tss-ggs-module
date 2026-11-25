<?php

namespace TSSGGSModule\App\Libs\Settings;

use TSSGGSModule\App\Libs\Billing\FinancePolicy;
use TSSGGSModule\App\Libs\Support\GeneralSettings;
use TSSGGSModule\App\Libs\Support\Helpers;
use TSSGGSModule\App\Libs\Billing\RenewalPrices;
use TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;

/**
 * Settings > Finance. The margin and the exchange rate live in "financeSettings" (read by the price update and the
 * order code), everything else in the general settings.
 */
class FinanceSettingsProvider extends Provider
{
    const TEXTS = 'admin.settings.index.providers.finance_settings_provider';

    const FINANCE  = ['profitMargin', 'rate'];
    const GENERAL  = ['minProfit', 'termMarginStep', 'priceRounding', 'keepMarginOnSync', 'belowCostAction', 'belowCostEmail', 'renewalPriceMode',
                      'renewalPriceClientEmail', 'renewalPriceEmailText', 'rateSource', 'currencyBuffer', 'balanceAlert', 'balanceThresholdTss', 'balanceThresholdGgs'];
    const SWITCHES = ['keepMarginOnSync', 'belowCostEmail', 'renewalPriceClientEmail', 'balanceAlert'];

    public function read()
    {
        $configuration = (new AddonModuleRepository())->getModuleConfiguration();
        foreach((array)($configuration['financeSettings'] ?? []) as $key => $value)
        {
            $this->data[$key] = $value;
        }
        foreach(self::GENERAL as $key)
        {
            $this->data[$key] = in_array($key, self::SWITCHES, true) ? GeneralSettings::enabled($key) : GeneralSettings::get($key);
        }
        if(trim((string)$this->data['renewalPriceEmailText']) === '')
        {
            $this->data['renewalPriceEmailText'] = RenewalPrices::defaultEmailText();
        }

        $options = function($prefix, array $values) {
            $result = [];
            foreach($values as $value)
            {
                $result[(string)$value] = $this->translate($prefix . '_' . $value);
            }

            return $result;
        };
        $this->availableValues['priceRounding']    = $options('rounding', FinancePolicy::ROUNDING);
        $this->availableValues['belowCostAction']  = $options('belowCost', FinancePolicy::BELOW_COST);
        $this->availableValues['renewalPriceMode'] = $options('renewal', FinancePolicy::RENEWAL_MODES);
        $this->availableValues['rateSource']       = $options('rateSource', FinancePolicy::RATE_SOURCES);
        $this->availableValues['currency'] = Helpers::getCurrencyOptions();
    }

    public function update()
    {
        $data = $this->formData->toArray();

        // financeSettings: only the posted keys change (the rate is posted only when WHMCS has no USD currency)
        $repository = new AddonModuleRepository();
        $finance    = (array)($repository->getModuleConfiguration()['financeSettings'] ?? []);
        foreach(self::FINANCE as $key)
        {
            if(array_key_exists($key, $data))
            {
                $finance[$key] = $key === 'profitMargin' ? max(0, (float)$data[$key]) : (float)str_replace(',', '.', (string)$data[$key]);
            }
        }
        $repository->saveFinanceSettings($finance);

        $general = array_intersect_key($data, array_flip(self::GENERAL));
        foreach(self::SWITCHES as $key)
        {
            if(array_key_exists($key, $general))
            {
                $general[$key] = !empty($general[$key]) && !in_array($general[$key], ['off', 'false', '0'], true);
            }
        }
        foreach(['minProfit', 'termMarginStep', 'currencyBuffer', 'balanceThresholdTss', 'balanceThresholdGgs'] as $key)
        {
            if(array_key_exists($key, $general))
            {
                $general[$key] = max(0, (float)str_replace(',', '.', (string)$general[$key]));
            }
        }
        // the default text is shown in the field; keep it empty when unchanged so later wording updates reach it
        if(isset($general['renewalPriceEmailText']) && trim(str_replace("\r", '', (string)$general['renewalPriceEmailText'])) === trim(RenewalPrices::defaultEmailText()))
        {
            $general['renewalPriceEmailText'] = '';
        }
        GeneralSettings::save($general);

        // the balance is read again now: the page shows it fresh and a new warning amount is checked at once
        \TSSGGSModule\App\Libs\Billing\BalanceMonitor::run(true, \TSSGGSModule\App\Libs\Gateway\SSLTrustCenterApi::PAGE_TIMEOUT);
    }
}
