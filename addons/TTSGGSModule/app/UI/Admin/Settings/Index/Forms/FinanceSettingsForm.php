<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Forms;


use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Components\RevertSwitcher;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Providers\ApiSettingsProvider;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Providers\FinanceSettingsProvider;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Providers\SslSettingsProvider;
use ModulesGarden\TTSGGSModule\Components\Alert\AlertInfo;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonPrimary;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TTSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TTSGGSModule\Components\Form\Form;
use ModulesGarden\TTSGGSModule\Components\FormInputPassword\FormInputPassword;
use ModulesGarden\TTSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TTSGGSModule\Components\Grid\Grid;
use ModulesGarden\TTSGGSModule\Components\Number\Number;
use ModulesGarden\TTSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TTSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;


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

        $exchangeRateWidget = new Widget();
        $exchangeRateWidget->setTitle($this->translate('exchangeRate'));
        $rightContainer->addElement($exchangeRateWidget);


        $this->builder->addFieldInContainer(
            $majorSettingsWidget,
            (new Number())
                ->setName('profitMargin')
                ->required()
                ->numeric()
                ->between(1, 1000)
        );

        $this->builder->addFieldInContainer(
            $majorSettingsWidget,
            (new Dropdown())
                ->setName('currency')
                ->required()
        );

        $this->builder->addFieldInContainer(
            $exchangeRateWidget,
            (new FormInputText())
                ->setName('rate')
        );

        $exchangeRateWidget->addElement(
            (new AlertInfo())
                ->setText($this->translate('info'))
                ->setId('financial-settings-form-info')
        );


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