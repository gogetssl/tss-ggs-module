<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms;


use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Concerns\SettingsLayout;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers\SslSettingsProvider;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TSSGGSModule\Components\Grid\Grid;
use ModulesGarden\TSSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\App\Libs\RenewalPolicy;
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
    use SettingsLayout;

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

        if($ssl)
        {
            $this->validationFields($this->section('sslValidation', 'format-list-checks'), $this->scope === self::SCOPE_ALL);
            $this->csrFields($this->section('csrGenerator', 'key-variant'));
        }

        if($this->scope === self::SCOPE_SSL)
        {
            $this->orderingFields($this->section('ordering', 'web'));
            $this->deliveryFields($this->section('delivery', 'file-document-outline'));
            $this->messageFields($this->section('clientMessages', 'email-outline'));
        }

        if($this->scope === self::SCOPE_AUTOMATED)
        {
            $this->autoInstallFields($this->section('autoInstall', 'cog-outline'));
        }

        if($ssl)
        {
            $this->renewalFields($this->section('renewals', 'calendar-month-outline'), $this->scope === self::SCOPE_ALL);
            $this->orderFields($this->section('recurringAndOneTime', 'credit-card-clock-outline'));
        }

        // the due dates are on Settings > Finance (Billing)

        if($this->scope === self::SCOPE_ALL || $this->scope === self::SCOPE_AUTOMATED)
        {
            $this->subscriptionFields($this->section('renewalsSubscription', 'calendar-month-outline'));
        }

        $this->saveBar(null, $this->scope === self::SCOPE_ALL);
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
            $this->builder->addFieldInContainer($widget, (new Dropdown())->setName('autoReissueDaysBeforeExpire'), true);
        }
        // daily e-mail to the administrators: installed, failed and long-waiting AutoInstall jobs (App\Libs\AutoInstallReport)
        $this->builder->addFieldInContainer($widget, (new Switcher())->setName('autoInstallReportEmail')->addClass('switcher-revert'), true);
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
                ->addClass('switcher-revert'),
            true
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
                ->setDefaultValueAsFirstOption(),
            true
        );
    }

    protected function renewalFields($widget, $withReissue)
    {
        foreach(['visibleRenewButtonBeforeExpiration', 'visibleRenewButtonAfterExpiration', 'renewOrderViaExistingOrder', 'renewReuseOrderDetails', 'setTheUnpaidStatusForRenewalInvoices'] as $name)
        {
            // with a hint ("<name>_description"); setTheUnpaidStatusForRenewalInvoices has none
            $this->builder->addFieldInContainer($widget, (new Switcher())->setName($name)->addClass('switcher-revert'), $name !== 'setTheUnpaidStatusForRenewalInvoices');
        }

        $this->builder->addFieldInContainer($widget, (new Dropdown())->setName('additionalDaysForRenewalOrder')->setDefaultValueAsFirstOption(), true);

        if($withReissue)
        {
            $this->builder->addFieldInContainer($widget, (new Dropdown())->setName('autoReissueDaysBeforeExpire'), true);
        }

    }

    /**
     * Renewal invoices and reminders of recurring and one-time orders (two sub-sections of one section)
     */
    protected function orderFields($widget)
    {
        $recurringOrdersWidget = $this->subSection('recurringOrders', $widget);

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
        $this->builder->addFieldInContainer($recurringContainer, (new Dropdown())->setName('recurringPaymentTerm'));
        $recurringContainer->addElement((new Text())->setText($this->renewalCheck(RenewalPolicy::CERTIFICATE)));
        // price of additional SANs bought after the order (App\Libs\UpgradePricing)
        $this->builder->addFieldInContainer($recurringOrdersWidget, (new Dropdown())->setName('sanUpgradePricing')->setDefaultValueAsFirstOption(), true);

        $oneTimeOrdersWidget = $this->subSection('oneTimeOrders', $widget);

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
        $recurringOrdersWidget = $this->subSection('recurringOrders', $widget);

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
        $this->builder->addFieldInContainer($recurringContainer, (new Dropdown())->setName('recurringPaymentTermSubscription'));
        $recurringContainer->addElement((new Text())->setText($this->renewalCheck(RenewalPolicy::SUBSCRIPTION)));
        $this->builder->addFieldInContainer($recurringContainer, (new Switcher())->setName('recurringSendExpirationNotificationsSubscription')->addClass('switcher-revert'));
        $this->builder->addFieldInContainer($recurringContainer, (new Switcher())->setName('terminateUnpaidSubscriptions')->addClass('switcher-revert'));
        $this->builder->addFieldInContainer($recurringContainer, (new Dropdown())->setName('pendingPaymentWaitSubscription'));
        if(RenewalPolicy::pendingWait(RenewalPolicy::SUBSCRIPTION) > 0)
        {
            // waiting past the renewal day: the vendor charges before the payment has cleared
            $recurringContainer->addElement((new Text())->setText('<div class="tssggs-renewal-check"><p class="tssggs-renewal-check__warning tssggs-renewal-check__warning--amber">'
                . htmlspecialchars($this->translate('pendingWaitSubscriptionRisk')) . '</p></div>'));
        }
        $this->builder->addFieldInContainer($recurringOrdersWidget, (new Dropdown())->setName('subscriptionSlotPricing')->setDefaultValueAsFirstOption());
    }

    // ---- fields of the general settings store that belong to SSL certificates ---- //

    protected function orderingFields($widget)
    {
        $this->builder->addFieldInContainer($widget, (new Switcher())->setName('useDefaultWebServer')->addClass('switcher-revert'), true);
        $this->builder->addFieldInContainer($widget, (new Switcher())->setName('requireAgreement')->addClass('switcher-revert'), true);

        // one agreement per certificate authority, picked automatically from the product's brand
        foreach(array_keys(\ModulesGarden\TSSGGSModule\App\Libs\CertificateService::AUTHORITIES) as $authority)
        {
            $sub = $this->subSection('agreement_' . $authority, $widget);
            $this->builder->addFieldInContainer($sub, (new FormInputText())->setName('agreementUrl_' . $authority), true);
            $this->builder->addFieldInContainer($sub, (new TextArea())->setName('agreementText_' . $authority), true);
        }
        // no "other certificate authorities" agreement: every product sold is Sectigo, DigiCert or GoGetSSL (an agreement
        // text saved there earlier still fills an empty one of these, CertificateService::agreement)
    }

    protected function deliveryFields($widget)
    {
        foreach(['zip', 'crt', 'ca', 'fullchain', 'nginx', 'apache', 'pkcs7'] as $format)
        {
            $this->builder->addFieldInContainer($widget, (new Switcher())->setName('downloadFormat_' . $format)->addClass('switcher-revert'), true);
        }
    }

    protected function messageFields($widget)
    {
        $this->builder->addFieldInContainer($widget, (new TextArea())->setName('processingGuide'), true);
        // "Issued SSL Message for": a certificate family (App\Libs\IssuedMessage) and a language. Only the
        // chosen boxes are shown (resources/assets/js/custom/issuedMessageFor.js); the selectors have no name, so they
        // are never saved. Each family / language box is prefilled with the built-in text (SslSettingsProvider::read)
        $issued    = \ModulesGarden\TSSGGSModule\App\Libs\IssuedMessage::class;
        $languages = $issued::languages();
        $options   = '';
        foreach($issued::FAMILIES as $family => $info)
        {
            $options .= '<option value="' . $family . '">' . htmlspecialchars($this->translate('issuedMessageFor_' . $family)) . '</option>';
        }
        // no "General (other certificates)": every SSL product sold is Sectigo or DigiCert (GoGetSSL-brand ones too), so
        // a family message always applies; a General message saved earlier still shows when a family's is switched off
        $languageOptions = '';
        foreach($languages as $language)
        {
            $languageOptions .= '<option value="' . htmlspecialchars($language) . '">' . htmlspecialchars(ucfirst($language)) . '</option>';
        }
        $widget->addElement((new Text())->setText(
            '<div class="tssggs-issued-for lu-form-group" data-savebar-ignore>'
            . '<div class="tssggs-issued-for__field"><label class="lu-form-label">' . htmlspecialchars($this->translate('issuedMessageFor')) . '</label>'
            . '<select class="lu-form-control tssggs-issued-for__select">' . $options . '</select></div>'
            . '<div class="tssggs-issued-for__field tssggs-issued-for__language"><label class="lu-form-label">' . htmlspecialchars($this->translate('issuedMessageLanguage')) . '</label>'
            . '<select class="lu-form-control tssggs-issued-for__language-select">' . $languageOptions . '</select></div></div>'
        ));
        foreach(array_keys($issued::FAMILIES) as $family)
        {
            $this->builder->addFieldInContainer($widget, (new Switcher())->setName($issued::SWITCH_PREFIX . $family)->addClass('switcher-revert'), true);
            $brand = $this->translate('issuedMessageFor_' . $family);
            foreach($languages as $language)
            {
                $hint = $issued::translated($language) ? 'issuedMessageText_description' : 'issuedMessageTextEnglish_description';
                $this->builder->addFieldInContainer($widget, (new TextArea())->setName($issued::textKey($family, $language))
                    ->setTitle(str_replace(['{brand}', '{language}'], [$brand, ucfirst($language)], $this->translate('issuedMessageText')))
                    ->setDescription($this->translate($hint)), true);
            }
        }
        $this->builder->addFieldInContainer($widget, (new Dropdown())->setName('certificateEmailTemplate'), true);
    }

    /**
     * What the lead and the payment term mean together, in words: a summary line and, when something is at risk, a
     * warning (App\Libs\RenewalPolicy::warning). Shown as saved; resources/assets/js/custom/renewalCheck.js redraws
     * it while the administrator changes the two fields. Every text is given here, translated - the script only puts
     * the numbers in.
     */
    protected function renewalCheck(string $type): string
    {
        [, $leadKey, $termKey] = RenewalPolicy::KEYS[$type];
        $lead   = RenewalPolicy::lead($type);
        $term   = RenewalPolicy::term($type);
        $prefix = $type === RenewalPolicy::SUBSCRIPTION ? 'renewalCheckSubscription' : 'renewalCheckCertificate';
        $texts  = [
            'before' => $this->translate($prefix . 'Before'),
            'on'     => $this->translate($prefix . 'On'),
            'after'  => $this->translate($prefix . 'After'),
            'amber'  => $this->translate($type === RenewalPolicy::SUBSCRIPTION ? 'renewalDueDayBeforeRenewal' : 'renewalDueAfterCertificateEnds'),
            'red'    => $this->translate('renewalDueAfterSubscriptionRenews'),
        ];

        $attributes = ' data-type="' . htmlspecialchars($type) . '" data-lead="' . htmlspecialchars($leadKey) . '" data-term="' . htmlspecialchars($termKey) . '"';
        foreach($texts as $name => $text)
        {
            $attributes .= ' data-text-' . $name . '="' . htmlspecialchars($text) . '"';
        }

        return '<div class="tssggs-renewal-check"' . $attributes . '>' . self::renewalCheckBody($type, $lead, $term, $texts) . '</div>';
    }

    /**
     * The inner HTML of a renewal check - the same rules as renewalCheck.js
     */
    public static function renewalCheckBody(string $type, int $lead, int $term, array $texts): string
    {
        $after   = $term - $lead;
        $vars    = [':lead' => $lead, ':term' => $term, ':days' => abs($after), ':safe' => RenewalPolicy::safeTerm($type, $lead)];
        $summary = strtr($after < 0 ? $texts['before'] : ($after === 0 ? $texts['on'] : $texts['after']), $vars);
        $html    = '<p class="tssggs-renewal-check__summary">' . htmlspecialchars($summary) . '</p>';

        $warning = RenewalPolicy::warning($type, $lead, $term);
        if($warning)
        {
            $text  = $warning['level'] === 'red' ? $texts['red'] : $texts['amber'];
            $html .= '<p class="tssggs-renewal-check__warning tssggs-renewal-check__warning--' . $warning['level'] . '">' . htmlspecialchars(strtr($text, $vars)) . '</p>';
        }

        return $html;
    }
}
