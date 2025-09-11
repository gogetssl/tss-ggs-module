<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms;


use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Components\RevertSwitcher;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers\ApiSettingsProvider;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers\FinanceSettingsProvider;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers\SslSettingsProvider;
use ModulesGarden\TSSGGSModule\Components\Alert\AlertInfo;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonPrimary;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\FormInputPassword\FormInputPassword;
use ModulesGarden\TSSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TSSGGSModule\Components\Grid\Grid;
use ModulesGarden\TSSGGSModule\Components\Number\Number;
use ModulesGarden\TSSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TSSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;


class FinanceSettingsForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
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

        $leftContainer  = new Container();
        $rightContainer = new Container();

        $grid = new Grid();
        $grid->setRows([
                           [
                               [$leftContainer, 6], [$rightContainer, 6],
                           ]
                       ]);
        $this->addElement($grid);

        $majorSettingsWidget = new Widget();
        $majorSettingsWidget->setTitle($this->translate('majorSettings'));
        $leftContainer->addElement($majorSettingsWidget);

        $this->builder->addFieldInContainer(
            $majorSettingsWidget,
            (new Number())
                ->setName('profitMargin')
                ->required()
                ->numeric()
                ->between(1, 1000)
        );

        if(!Helpers::getCurrencyIdByCode('USD')) //If USD not defined
        {
            $exchangeRateWidget = new Widget();
            $exchangeRateWidget->setTitle($this->translate('exchangeRate'));
            $rightContainer->addElement($exchangeRateWidget);

            $this->builder->addFieldInContainer(
                $exchangeRateWidget,
                (new FormInputText())
                    ->setName('rate')
                    ->required()
            );

            $exchangeRateWidget->addElement(
                (new AlertInfo())
                    ->setText($this->translate('info', [':defaultCurrency' => Helpers::getDefaultCurrencyCode()]))
                    ->setId('financial-settings-form-info')
            );
        }

        $toolbar = new Toolbar();
        $toolbar->addElement(
            (new ButtonSuccess())
                ->setId('buttonSave')
                ->setTitle($this->translate('save'))
                ->onClick(new FormSubmit($this))
        );

        $this->addElement($toolbar);
    }
}