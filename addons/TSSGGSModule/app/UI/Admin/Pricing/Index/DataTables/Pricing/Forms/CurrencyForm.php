<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Pricing\Index\DataTables\Pricing\Forms;


use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Pricing\Index\DataTables\Pricing\Providers\CurrencyProvider;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Pricing\Index\DataTables\Pricing\Providers\PricingProvider;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TSSGGSModule\Components\HiddenField\HiddenField;
use ModulesGarden\TSSGGSModule\Components\Icon\Icon;
use ModulesGarden\TSSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TSSGGSModule\Components\Tab\Tab;
use ModulesGarden\TSSGGSModule\Components\TabsWidget\TabsWidget;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TSSGGSModule\Core\Components\FormFields\FormField;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Currency;


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