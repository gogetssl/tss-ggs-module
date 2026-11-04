<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms;


use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
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
        $this->builder->addFieldInContainer($pricing, (new Number())->setName('profitMargin')->setMin(0)->setMax(1000)->required()->numeric()->between(0, 1000), true);
        $this->builder->addFieldInContainer($pricing, (new FormInputText())->setName('minProfit')->numeric()->between(0, 100000), true);
        $this->builder->addFieldInContainer($pricing, (new Number())->setName('termMarginStep')->setMin(0)->setMax(100)->numeric()->between(0, 100), true);
        $this->builder->addFieldInContainer($pricing, (new Dropdown())->setName('priceRounding'), true);
        $this->builder->addFieldInContainer($pricing, (new Switcher())->setName('keepMarginOnSync')->addClass('switcher-revert'), true);

        $belowCost = $this->section('belowCost', 'alert-circle-outline');
        $this->builder->addFieldInContainer($belowCost, (new Dropdown())->setName('belowCostAction'), true);
        $this->builder->addFieldInContainer($belowCost, (new Switcher())->setName('belowCostEmail')->addClass('switcher-revert'), true);

        $renewals = $this->section('renewalPrices', 'refresh');
        $this->builder->addFieldInContainer($renewals, (new Dropdown())->setName('renewalPriceMode'), true);
        $this->builder->addFieldInContainer($renewals, (new Switcher())->setName('renewalPriceClientEmail')->addClass('switcher-revert'), true);
        $this->builder->addFieldInContainer($renewals, (new TextArea())->setName('renewalPriceEmailText'), true);

        $currency = $this->section('currency', 'currency-usd');
        if(!Helpers::getCurrencyIdByCode('USD')) // the gateway prices are in USD: without a USD currency the module converts with its own rate
        {
            $this->builder->addFieldInContainer($currency, (new Dropdown())->setName('rateSource'), true);
            $this->builder->addFieldInContainer($currency, (new FormInputText())->setName('rate')->required()->numeric(), true);
        }
        $this->builder->addFieldInContainer($currency, (new FormInputText())->setName('currencyBuffer')->numeric()->between(0, 50), true);

        $billing = $this->section('billing', 'credit-card-clock-outline');
        $this->builder->addFieldInContainer($billing, (new Switcher())->setName('terminateUnpaidOnExpiry')->addClass('switcher-revert'), true);
        $this->builder->addFieldInContainer($billing, (new Dropdown())->setName('dueDateOffsetRegular'), true);
        $this->builder->addFieldInContainer($billing, (new Dropdown())->setName('dueDateOffsetSubscription'), true);

        $balance = $this->section('balance', 'hand-coin-outline');
        $this->builder->addFieldInContainer($balance, (new Switcher())->setName('balanceAlert')->addClass('switcher-revert'), true);

        $this->saveBar();
    }
}
