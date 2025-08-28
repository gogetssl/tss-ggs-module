<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Pricing\Index\DataTables\Pricing\Forms;


use ModulesGarden\TTSGGSModule\App\Libs\Helpers;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Pricing\Index\DataTables\Pricing\Providers\CurrencyProvider;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Pricing\Index\DataTables\Pricing\Providers\PricingProvider;
use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TTSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TTSGGSModule\Components\Form\Form;
use ModulesGarden\TTSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TTSGGSModule\Components\HiddenField\HiddenField;
use ModulesGarden\TTSGGSModule\Components\Icon\Icon;
use ModulesGarden\TTSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TTSGGSModule\Components\Tab\Tab;
use ModulesGarden\TTSGGSModule\Components\TabsWidget\TabsWidget;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TTSGGSModule\Core\Components\FormFields\FormField;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Currency;


class CurrencyForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->provider       = CurrencyProvider::class;
        $this->providerAction = CurrencyProvider::ACTION_UPDATE;
    }

    public function loadHtml(): void
    {
        $this->setId('PricingPricingForm');
        $this->builder = BuilderCreator::twoColumns($this);

        $currencies = Currency::get();
        $options    = [];

        foreach($currencies as $currency)
        {
            $options[$currency->code] = $currency->code;
        }

        $this->builder->addField(
            (new Dropdown())
                ->setTitle('Currency')
                ->setName('currency')
                ->setOptions($options)
                ->setDefaultValueAsFirstOption()
                ->onChange(
                    (new FormSubmit($this))
                )
        );

    }
}