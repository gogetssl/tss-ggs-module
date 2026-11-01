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
use ModulesGarden\TSSGGSModule\Components\TextArea\TextArea;
use ModulesGarden\TSSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\Collapse;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

/**
 * Settings of one product category. $scope:
 *  - 'ssl'       SSL certificates (validation, CSR generator, renewals, due dates, ordering, downloads, messages)
 *  - 'automated' automated SSL: AutoInstall (cPanel) and subscriptions (ACME / AutoInstall plans)
 *  - 'all'       everything of both (the installation wizard)
 * The fields of the "ssl" scope that belong to the general settings store are saved by SslSettingsProvider.
 */
class SslSettingsForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    const SCOPE_ALL       = 'all';
    const SCOPE_SSL       = 'ssl';
    const SCOPE_AUTOMATED = 'automated';

    protected $configuration;
    protected $scope;

    public function __construct($configuration = false, $scope = self::SCOPE_ALL)
    {
        parent::__construct();

        $this->configuration  = $configuration;
        $this->scope          = $scope;
        $this->provider       = SslSettingsProvider::class;
        $this->providerAction = SslSettingsProvider::ACTION_UPDATE;
    }

    public function loadHtml(): void
    {
        $this->builder = BuilderCreator::oneColumn($this);
        $this->setId('SslSettingsForm');

        $ssl  = in_array($this->scope, [self::SCOPE_ALL, self::SCOPE_SSL], true);
        $auto = in_array($this->scope, [self::SCOPE_ALL, self::SCOPE_AUTOMATED], true);

        $leftContainer  = new Container();
        $rightContainer = new Container();

        $grid = new Grid();
        $grid->setRows([[[$leftContainer, 6], [$rightContainer, 6]]]);
        $this->addElement($grid);

        if($ssl)
        {
            $sslValidationWidget = $this->widget($leftContainer, 'sslValidation');
            $this->validationFields($sslValidationWidget, $this->scope === self::SCOPE_ALL);
        }

        if($this->scope === self::SCOPE_SSL)
        {
            $this->orderingFields($this->widget($leftContainer, 'ordering'));
            $this->deliveryFields($this->widget($leftContainer, 'delivery'));
        }

        if($this->scope === self::SCOPE_AUTOMATED)
        {
            $this->autoInstallFields($this->widget($leftContainer, 'autoInstall'));
        }

        if($ssl)
        {
            $this->csrFields($this->widget($rightContainer, 'csrGenerator'));
            $this->renewalFields($this->widget($rightContainer, 'renewals'), $this->scope === self::SCOPE_ALL);
        }

        if($this->scope === self::SCOPE_SSL)
        {
            $this->dueDateFields($this->widget($rightContainer, 'dueDates'));
        }

        if($auto)
        {
            $this->subscriptionFields($this->widget($rightContainer, 'renewalsSubscription'));
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

    protected function widget(Container $container, $title)
    {
        $widget = new Widget();
        $widget->setTitle($this->translate($title));
        $container->addElement($widget);

        return $widget;
    }

    protected function validationFields($widget, $withAutomated)
    {
        $this->builder->addFieldInContainer($widget, (new Switcher())->setName('disableEmailValidation')->addClass('switcher-revert'), true);
        $this->builder->addFieldInContainer($widget, (new Switcher())->setName('autoDetailsForDvOrders')->addClass('switcher-revert'), true);

        if($withAutomated)
        {
            $this->autoInstallFields($widget, false);
        }

        $useFixedTechnicalDetails = $this->builder->addFieldInContainer(
            $widget,
            (new Switcher())
                ->setName('useFixedTechnicalDetails')
                ->addClass('switcher-revert'),
            true
        );

        $detailsContainer = new Container();
        $widget->addElement($detailsContainer);

        $useFixedTechnicalDetails->onChange(
            (new Collapse($detailsContainer, !$this->provider()->getValueById('useFixedTechnicalDetails')))
        );

        foreach(['firstName', 'lastName', 'companyName', 'jobTitle', 'emailAddress', 'address', 'city', 'zipCode', 'stateRegion', 'postCode'] as $name)
        {
            $this->builder->addFieldInContainer(
                $detailsContainer,
                (new FormInputText())
                    ->setName($name)
                    ->addValidator('required_if:useFixedTechnicalDetails,1')
                    ->setTranslationCustomAttributes(['useFixedTechnicalDetails'], $this)
                    ->setTranslationCustomValues(['useFixedTechnicalDetails' => ["1"]], $this)
            );
        }

        $this->builder->addFieldInContainer(
            $detailsContainer,
            (new Dropdown())
                ->setName('country')
                ->addValidator('required_if:useFixedTechnicalDetails,1')
                ->setTranslationCustomAttributes(['useFixedTechnicalDetails'], $this)
                ->setTranslationCustomValues(['useFixedTechnicalDetails' => ["1"]], $this)
        );
    }

    /**
     * AutoInstall (cPanel): DNS wait for wildcard names, TLS check of the cPanel servers and the automatic reissue
     */
    protected function autoInstallFields($widget, $withReissue = true)
    {
        $this->builder->addFieldInContainer($widget, (new FormInputText())->setName('wildcardDnsTimeoutMinutes'), true);
        $this->builder->addFieldInContainer($widget, (new Switcher())->setName('cpanelTlsVerifyDisabled')->setDefaultValue(false), true);

        if($withReissue)
        {
            $this->builder->addFieldInContainer($widget, (new Dropdown())->setName('autoReissueDaysBeforeExpire'));
        }
    }

    protected function csrFields($widget)
    {
        $enableCsrGenerator = $this->builder->addFieldInContainer(
            $widget,
            (new Switcher())
                ->setName('enableCsrGenerator')
                ->addClass('switcher-revert')
        );

        $csrGeneratorContainer = new Container();
        $widget->addElement($csrGeneratorContainer);
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
    }

    protected function renewalFields($widget, $withReissue)
    {
        foreach(['visibleRenewButtonBeforeExpiration', 'visibleRenewButtonAfterExpiration', 'renewOrderViaExistingOrder', 'automaticProcessingOfRenewalOrders', 'setTheUnpaidStatusForRenewalInvoices'] as $name)
        {
            $this->builder->addFieldInContainer($widget, (new Switcher())->setName($name)->addClass('switcher-revert'));
        }

        $this->builder->addFieldInContainer($widget, (new Dropdown())->setName('additionalDaysForRenewalOrder')->setDefaultValueAsFirstOption());

        if($withReissue)
        {
            $this->builder->addFieldInContainer($widget, (new Dropdown())->setName('autoReissueDaysBeforeExpire'));
        }

        $recurringOrdersWidget = new Widget();
        $recurringOrdersWidget->setTitle($this->translate('recurringOrders'));
        $widget->addElement($recurringOrdersWidget);

        $recurringSwitcher = $this->builder->addFieldInContainer(
            $recurringOrdersWidget,
            (new Switcher())
                ->setName('recurringCreateAutomaticRenewalInvoice')
                ->addClass('switcher-revert')
        );

        // reminders do not depend on the invoice switch: switch off + days = "reminder only", switch on = full automatic renewal
        $this->builder->addFieldInContainer(
            $recurringOrdersWidget,
            (new FormInputText())
                ->setName('recurringSendExpirationNotifications'),
            true
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

        $oneTimeOrdersWidget = new Widget();
        $oneTimeOrdersWidget->setTitle($this->translate('oneTimeOrders'));
        $widget->addElement($oneTimeOrdersWidget);

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

        $this->builder->addFieldInContainer($oneTimeContainer, (new Dropdown())->setName('oneTimeDaysBeforeExpiry')->setDefaultValueAsFirstOption());
        $this->builder->addFieldInContainer($oneTimeContainer, (new FormInputText())->setName('oneTimeSendExpirationNotifications'), true);
    }

    /**
     * Subscriptions (ACME / AutoInstall plans): renewal invoice and reminders
     */
    protected function subscriptionFields($widget)
    {
        $recurringOrdersWidget = new Widget();
        $recurringOrdersWidget->setTitle($this->translate('recurringOrders'));
        $widget->addElement($recurringOrdersWidget);

        $recurringSwitcher = $this->builder->addFieldInContainer(
            $recurringOrdersWidget,
            (new Switcher())
                ->setName('recurringCreateAutomaticRenewalInvoiceSubscription')
                ->addClass('switcher-revert')
        );

        $recurringContainer = new Container();
        $recurringOrdersWidget->addElement($recurringContainer);
        $recurringSwitcher->onChange(
            (new Collapse($recurringContainer, !$this->provider()->getValueById('recurringCreateAutomaticRenewalInvoiceSubscription')))
        );

        $this->builder->addFieldInContainer($recurringContainer, (new Dropdown())->setName('recurringDaysBeforeExpirySubscription')->setDefaultValueAsFirstOption());
        $this->builder->addFieldInContainer($recurringContainer, (new Switcher())->setName('recurringSendExpirationNotificationsSubscription')->addClass('switcher-revert'));
    }

    // ---- fields of the general settings store that belong to SSL certificates ---- //

    protected function orderingFields($widget)
    {
        $this->builder->addFieldInContainer($widget, (new FormInputText())->setName('defaultWebServer'), true);
        $this->builder->addFieldInContainer($widget, (new Switcher())->setName('requireAgreement')->addClass('switcher-revert'), true);
        $this->builder->addFieldInContainer($widget, (new TextArea())->setName('agreementText'), true);
    }

    protected function deliveryFields($widget)
    {
        foreach(['zip', 'crt', 'ca', 'fullchain', 'nginx', 'apache', 'pkcs7'] as $format)
        {
            $this->builder->addFieldInContainer($widget, (new Switcher())->setName('downloadFormat_' . $format)->addClass('switcher-revert'), true);
        }
        $this->builder->addFieldInContainer($widget, (new TextArea())->setName('processingGuide'), true);
        $this->builder->addFieldInContainer($widget, (new TextArea())->setName('issuedMessage'), true);
        $this->builder->addFieldInContainer($widget, (new Dropdown())->setName('certificateEmailTemplate'), true);
    }

    protected function dueDateFields($widget)
    {
        $this->builder->addFieldInContainer($widget, (new Dropdown())->setName('dueDateOffsetRegular'), true);
        $this->builder->addFieldInContainer($widget, (new Dropdown())->setName('dueDateOffsetSubscription'), true);
    }
}
