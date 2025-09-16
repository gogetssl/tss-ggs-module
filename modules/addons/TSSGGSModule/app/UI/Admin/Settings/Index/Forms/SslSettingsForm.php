<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms;


use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Components\RevertSwitcher;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers\SslSettingsProvider;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TSSGGSModule\Components\Grid\Grid;
use ModulesGarden\TSSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TSSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\Collapse;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;


class SslSettingsForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    protected $configuration;

    public function __construct($configuration = false)
    {
        parent::__construct();

        $this->configuration  = $configuration;
        $this->provider       = SslSettingsProvider::class;
        $this->providerAction = SslSettingsProvider::ACTION_UPDATE;
    }

    public function loadHtml(): void
    {
        $this->builder = BuilderCreator::oneColumn($this);
        $this->setId('SslSettingsForm');

        $leftContainer  = new Container();
        $rightContainer = new Container();

        $grid = new Grid();
        $grid->setRows([
                           [
                               [$leftContainer, 6], [$rightContainer, 6],
                           ]
                       ]);
        $this->addElement($grid);

        $sslValidationWidget = new Widget();
        $sslValidationWidget->setTitle($this->translate('sslValidation'));
        $leftContainer->addElement($sslValidationWidget);

        $csrGeneratorWidget = new Widget();
        $csrGeneratorWidget->setTitle($this->translate('csrGenerator'));
        $rightContainer->addElement($csrGeneratorWidget);

        $renewalsWidget = new Widget();
        $renewalsWidget->setTitle($this->translate('renewals'));
        $rightContainer->addElement($renewalsWidget);

        //ssl validation------------------------------------------------------------------------------------------------

        $this->builder->addFieldInContainer(
            $sslValidationWidget,
            (new Switcher())
                ->setName('disableEmailValidation')
                ->addClass('switcher-revert'),
            true
        );

        $this->builder->addFieldInContainer(
            $sslValidationWidget,
            (new Switcher())
                ->setName('autoDetailsForDvOrders')
                ->addClass('switcher-revert'),
            true
        );

        $useFixedTechnicalDetails = $this->builder->addFieldInContainer(
            $sslValidationWidget,
            (new Switcher())
                ->setName('useFixedTechnicalDetails')
                ->addClass('switcher-revert'),
            true
        );

        $detailsContainer = new Container();
        $sslValidationWidget->addElement($detailsContainer);

        $useFixedTechnicalDetails->onChange(
            (new Collapse($detailsContainer, !$this->provider()->getValueById('useFixedTechnicalDetails')))
        );

        $this->builder->addFieldInContainer(
            $detailsContainer,
            (new FormInputText())
                ->setName('firstName')
                ->addValidator('required_if:useFixedTechnicalDetails,1')
                ->setTranslationCustomAttributes(['useFixedTechnicalDetails'], $this)
                ->setTranslationCustomValues(['useFixedTechnicalDetails' => ["1"]], $this)
        );

        $this->builder->addFieldInContainer(
            $detailsContainer,
            (new FormInputText())
                ->setName('lastName')
                ->addValidator('required_if:useFixedTechnicalDetails,1')
                ->setTranslationCustomAttributes(['useFixedTechnicalDetails'], $this)
                ->setTranslationCustomValues(['useFixedTechnicalDetails' => ["1"]], $this)
        );

        $this->builder->addFieldInContainer(
            $detailsContainer,
            (new FormInputText())
                ->setName('companyName')
                ->addValidator('required_if:useFixedTechnicalDetails,1')
                ->setTranslationCustomAttributes(['useFixedTechnicalDetails'], $this)
                ->setTranslationCustomValues(['useFixedTechnicalDetails' => ["1"]], $this)
        );

        $this->builder->addFieldInContainer(
            $detailsContainer,
            (new FormInputText())
                ->setName('jobTitle')
                ->addValidator('required_if:useFixedTechnicalDetails,1')
                ->setTranslationCustomAttributes(['useFixedTechnicalDetails'], $this)
                ->setTranslationCustomValues(['useFixedTechnicalDetails' => ["1"]], $this)
        );

        $this->builder->addFieldInContainer(
            $detailsContainer,
            (new FormInputText())
                ->setName('emailAddress')
                ->addValidator('required_if:useFixedTechnicalDetails,1')
                ->setTranslationCustomAttributes(['useFixedTechnicalDetails'], $this)
                ->setTranslationCustomValues(['useFixedTechnicalDetails' => ["1"]], $this)
        );

        $this->builder->addFieldInContainer(
            $detailsContainer,
            (new FormInputText())
                ->setName('address')
                ->addValidator('required_if:useFixedTechnicalDetails,1')
                ->setTranslationCustomAttributes(['useFixedTechnicalDetails'], $this)
                ->setTranslationCustomValues(['useFixedTechnicalDetails' => ["1"]], $this)
        );

        $this->builder->addFieldInContainer(
            $detailsContainer,
            (new FormInputText())
                ->setName('city')
                ->addValidator('required_if:useFixedTechnicalDetails,1')
                ->setTranslationCustomAttributes(['useFixedTechnicalDetails'], $this)
                ->setTranslationCustomValues(['useFixedTechnicalDetails' => ["1"]], $this)
        );

        $this->builder->addFieldInContainer(
            $detailsContainer,
            (new FormInputText())
                ->setName('zipCode')
                ->addValidator('required_if:useFixedTechnicalDetails,1')
                ->setTranslationCustomAttributes(['useFixedTechnicalDetails'], $this)
                ->setTranslationCustomValues(['useFixedTechnicalDetails' => ["1"]], $this)
        );

        $this->builder->addFieldInContainer(
            $detailsContainer,
            (new FormInputText())
                ->setName('stateRegion')
                ->addValidator('required_if:useFixedTechnicalDetails,1')
                ->setTranslationCustomAttributes(['useFixedTechnicalDetails'], $this)
                ->setTranslationCustomValues(['useFixedTechnicalDetails' => ["1"]], $this)
        );

        $this->builder->addFieldInContainer(
            $detailsContainer,
            (new FormInputText())
                ->setName('postCode')
                ->addValidator('required_if:useFixedTechnicalDetails,1')
                ->setTranslationCustomAttributes(['useFixedTechnicalDetails'], $this)
                ->setTranslationCustomValues(['useFixedTechnicalDetails' => ["1"]], $this)
        );

        $this->builder->addFieldInContainer(
            $detailsContainer,
            (new Dropdown())
                ->setName('country')
                ->addValidator('required_if:useFixedTechnicalDetails,1')
                ->setTranslationCustomAttributes(['useFixedTechnicalDetails'], $this)
                ->setTranslationCustomValues(['useFixedTechnicalDetails' => ["1"]], $this)
        );

        //csr generator-------------------------------------------------------------------------------------------------

        $enableCsrGenerator = $this->builder->addFieldInContainer(
            $csrGeneratorWidget,
            (new Switcher())
                ->setName('enableCsrGenerator')
                ->addClass('switcher-revert')
        );

        $csrGeneratorContainer = new Container();
        $csrGeneratorWidget->addElement($csrGeneratorContainer);
        $enableCsrGenerator->onChange(
            (new Collapse($csrGeneratorContainer, !$this->provider()->getValueById('enableCsrGenerator')))
        );


        $useProfileDetailsForCsr = $this->builder->addFieldInContainer(
            $csrGeneratorContainer,
            (new Switcher())
                ->setName('useProfileDetailsForCsr')
                ->addClass('switcher-revert')
        );

        $countryContainer = new Container();
        $csrGeneratorContainer->addElement($countryContainer);
        $useProfileDetailsForCsr->onChange(
            (new Collapse($countryContainer, $this->provider()->getValueById('useProfileDetailsForCsr')))
        );

        $this->builder->addFieldInContainer(
            $countryContainer,
            (new Dropdown())
                ->setName('defaultCsrCountry')
                ->setDefaultValueAsFirstOption()
                ->required()
        );

        //renewals------------------------------------------------------------------------------------------------------

        $this->builder->addFieldInContainer(
            $renewalsWidget,
            (new Switcher())
                ->setName('visibleRenewButtonBeforeExpiration')
                ->addClass('switcher-revert')
        );

        $this->builder->addFieldInContainer(
            $renewalsWidget,
            (new Switcher())
                ->setName('visibleRenewButtonAfterExpiration')
                ->addClass('switcher-revert')
        );

        $this->builder->addFieldInContainer(
            $renewalsWidget,
            (new Switcher())
                ->setName('renewOrderViaExistingOrder')
                ->addClass('switcher-revert')
        );

        $this->builder->addFieldInContainer(
            $renewalsWidget,
            (new Switcher())
                ->setName('automaticProcessingOfRenewalOrders')
                ->addClass('switcher-revert')
        );

        $this->builder->addFieldInContainer(
            $renewalsWidget,
            (new Switcher())
                ->setName('setTheUnpaidStatusForRenewalInvoices')
                ->addClass('switcher-revert')
        );

        $this->builder->addFieldInContainer(
            $renewalsWidget,
            (new Dropdown())
                ->setName('additionalDaysForRenewalOrder')
                ->setDefaultValueAsFirstOption()
        );

        $recurringOrdersWidget = new Widget();
        $recurringOrdersWidget->setTitle($this->translate('recurringOrders'));
        $renewalsWidget->addElement($recurringOrdersWidget);

        $recurringSwitcher = $this->builder->addFieldInContainer(
            $recurringOrdersWidget,
            (new Switcher())
                ->setName('recurringCreateAutomaticRenewalInvoice')
                ->addClass('switcher-revert')
        );

        $recurringContainer = new Container();
        $recurringOrdersWidget->addElement($recurringContainer);
        $recurringSwitcher->onChange(
            (new Collapse($recurringContainer, !$this->provider()->getValueById('recurringCreateAutomaticRenewalInvoice')))
        );

        $this->builder->addFieldInContainer(
            $recurringContainer,
            (new Dropdown())
                ->setName('recurringDaysBeforeExpiry')
                ->setDefaultValueAsFirstOption()
        );

        $this->builder->addFieldInContainer(
            $recurringContainer,
            (new FormInputText())
                ->setName('recurringSendExpirationNotifications'),
            true
        );

        $oneTimeOrdersWidget = new Widget();
        $oneTimeOrdersWidget->setTitle($this->translate('oneTimeOrders'));
        $renewalsWidget->addElement($oneTimeOrdersWidget);

        $oneTimeSwitcher = $this->builder->addFieldInContainer(
            $oneTimeOrdersWidget,
            (new Switcher())
                ->setName('oneTimeCreateAutomaticRenewalInvoice')
                ->addClass('switcher-revert')
        );

        $oneTimeContainer = new Container();
        $oneTimeOrdersWidget->addElement($oneTimeContainer);
        $oneTimeSwitcher->onChange(
            (new Collapse($oneTimeContainer, !$this->provider()->getValueById('oneTimeCreateAutomaticRenewalInvoice')))
        );

        $this->builder->addFieldInContainer(
            $oneTimeContainer,
            (new Dropdown())
                ->setName('oneTimeDaysBeforeExpiry')
                ->setDefaultValueAsFirstOption()
        );

        $this->builder->addFieldInContainer(
            $oneTimeContainer,
            (new FormInputText())
                ->setName('oneTimeSendExpirationNotifications'),
            true
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