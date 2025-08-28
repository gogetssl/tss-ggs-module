<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Pricing\Index\DataTables\Pricing\Forms;


use ModulesGarden\TTSGGSModule\App\Libs\Helpers;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Pricing\Index\DataTables\Pricing\Providers\PricingProvider;
use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TTSGGSModule\Components\Form\Form;
use ModulesGarden\TTSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TTSGGSModule\Components\HiddenField\HiddenField;
use ModulesGarden\TTSGGSModule\Components\Icon\Icon;
use ModulesGarden\TTSGGSModule\Components\Label\Label;
use ModulesGarden\TTSGGSModule\Components\Row\Row;
use ModulesGarden\TTSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TTSGGSModule\Components\Tab\Tab;
use ModulesGarden\TTSGGSModule\Components\TabsWidget\TabsWidget;
use ModulesGarden\TTSGGSModule\Components\Text\Text;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Components\FormFields\FormField;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Currency;


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


        $this->addElement($tabs);


    }
}