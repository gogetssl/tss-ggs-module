<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Pricing\Index\DataTables\Pricing\Forms;


use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Pricing\Index\DataTables\Pricing\Providers\PricingProvider;
use ModulesGarden\TSSGGSModule\Components\Alert\AlertInfo;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TSSGGSModule\Components\HiddenField\HiddenField;
use ModulesGarden\TSSGGSModule\Components\Icon\Icon;
use ModulesGarden\TSSGGSModule\Components\Label\Label;
use ModulesGarden\TSSGGSModule\Components\Row\Row;
use ModulesGarden\TSSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TSSGGSModule\Components\Tab\Tab;
use ModulesGarden\TSSGGSModule\Components\TabsWidget\TabsWidget;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Components\FormFields\FormField;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Currency;


class PricingForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->provider       = PricingProvider::class;
        $this->providerAction = PricingProvider::ACTION_UPDATE;
    }

    public function loadHtml(): void
    {
        $this->setId('PricingPricingForm');

        $tabs       = new TabsWidget();
        $currencies = Currency::get();

        $formData       = Request::get('formData');
        $whmcsProductId = $formData['whmcsProductId'];
        $currencyCode   = $formData['currencyCode'];

        foreach($currencies as $currency)
        {
            $tab = (new Tab())->setTitle($currency->code);
            $tab->setId($currency->code.'Tab');

            //Helpers::debugLog('form', $currencyCode, $currency->code);

            if($currency->code == $currencyCode)
            {
                $tab->setActive(true);
            }
            else
            {
                $tab->setActive(false);
            }

            $tabs->addTab($tab);

            $innerContainer = new Container();
            $innerContainer->setId('innerContainer');
            $innerContainer->addClass('innerContainer');
            $tab->addElement($innerContainer);

            $this->builder = BuilderCreator::threeColumnsInContainer($this, $innerContainer);
            $this->builder->addField((new FormInputText())->setName($currency->code . '_annually')->setTitle($this->translate('annually')));
            $this->builder->addField((new FormInputText())->setName($currency->code . '_biennially')->setTitle($this->translate('biennially')));
            $this->builder->addField((new FormInputText())->setName($currency->code . '_triennially')->setTitle($this->translate('triennially')));

            $this->builder->addField((new Switcher())->setName($currency->code . '_annually_enable')->setTitle(' ')->addClass('switcher-revert'));
            $this->builder->addField((new Switcher())->setName($currency->code . '_biennially_enable')->setTitle(' ')->addClass('switcher-revert'));
            $this->builder->addField((new Switcher())->setName($currency->code . '_triennially_enable')->setTitle(' ')->addClass('switcher-revert'));

            $configurableOptionsData = Helpers::getConfigurableOptionsData($whmcsProductId, $currency->id);

            foreach($configurableOptionsData as $configurableOptionData)
            {
                $parts      = explode('|', $configurableOptionData->optionname);
                $optionName = $parts[1] ?: $parts[0];
                $widget     = new Widget();
                $widget->setTitle($optionName);

                $widgetBuilder = BuilderCreator::threeColumnsInContainer($this, $widget);

                $widgetBuilder->addField((new FormInputText())->setName($currency->code . '_annually_option_' . $configurableOptionData->subId)->setTitle($this->translate('annually')));
                $widgetBuilder->addField((new FormInputText())->setName($currency->code . '_biennially_option_' . $configurableOptionData->subId)->setTitle($this->translate('biennially')));
                $widgetBuilder->addField((new FormInputText())->setName($currency->code . '_triennially_option_' . $configurableOptionData->subId)->setTitle($this->translate('triennially')));

                $this->builder->addElement($widget);
            }
        }


        $tab = (new Tab())->setTitle($this->translate('options'));
        $tab->setId('optionsTab');
        $tabs->addTab($tab);
        $innerContainer = new Container();
        $innerContainer->setId('innerContainer');
        $innerContainer->addClass('innerContainer');
        $tab->addElement($innerContainer);
        $this->builder = BuilderCreator::threeColumnsInContainer($this, $innerContainer);
        $this->builder->addField((new HiddenField)->setName('whmcsProductId'));
        $this->builder->addFieldInContainer($innerContainer, (new Switcher())->setName('auto_update_enable')->setTitle($this->translate('autoUpdate'))->addClass('switcher-revert'));


        $innerContainer->addElement(
            (new AlertInfo())
                ->setText($this->translate('info', [':defaultCurrency' => Helpers::getDefaultCurrencyCode()]))
                ->setId('financial-settings-form-info')
        );


        $this->addElement($tabs);


    }
}