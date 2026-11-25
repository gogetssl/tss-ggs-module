<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers;

use ModulesGarden\TSSGGSModule\App\Libs\Settings\GeneralSettingsProvider;
use ModulesGarden\TSSGGSModule\App\Libs\AutoInstall\AutoInstallHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\Support\GeneralSettings;
use ModulesGarden\TSSGGSModule\App\Libs\Certificates\IssuedMessage;
use ModulesGarden\TSSGGSModule\App\Libs\Billing\RenewalPolicy;
use ModulesGarden\TSSGGSModule\App\Libs\Support\Helpers;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\App\Libs\Dashboard\NextSteps;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Validator;


class SslSettingsProvider extends CrudProvider
{
    /** Default CSR Country: no fixed country, the client's own one is used */
    const CSR_COUNTRY_CLIENT = 'client';

    public function read()
    {
        $moduleRepository    = new AddonModuleRepository();
        $moduleConfiguration = $moduleRepository->getModuleConfiguration();

        foreach($moduleConfiguration['sslSettings'] as $key => $value)
        {
            $this->data[$key] = $value;
        }

        // renewal invoices (App\Libs\Billing\RenewalPolicy): created 1 to 30 days before the order end, due a payment term
        // later. What is shown is what applies - a lead saved above 30 before 1.5.0 works as 30.
        foreach([RenewalPolicy::CERTIFICATE, RenewalPolicy::SUBSCRIPTION] as $type)
        {
            [, $leadKey, $termKey] = RenewalPolicy::KEYS[$type];
            $this->data[$leadKey]  = RenewalPolicy::lead($type);
            $this->data[$termKey]  = RenewalPolicy::term($type);
        }
        // subscriptions: the renewal invoice and the termination of unpaid renewals are on unless turned off
        foreach(['recurringCreateAutomaticRenewalInvoiceSubscription', 'terminateUnpaidSubscriptions'] as $key)
        {
            if(!isset($this->data[$key]))
            {
                $this->data[$key] = 'on';
            }
        }
        if(empty($this->data['sanUpgradePricing']))
        {
            $this->data['sanUpgradePricing'] = \ModulesGarden\TSSGGSModule\App\Libs\Billing\SanUpgrade::PRICING_FULL;
        }
        if(empty($this->data['subscriptionSlotPricing']))
        {
            $this->data['subscriptionSlotPricing'] = \ModulesGarden\TSSGGSModule\App\Libs\Subscriptions\SubscriptionBilling::SLOT_PRICING_PRORATED;
        }

        if(!isset($this->data['wildcardDnsTimeoutMinutes']) || $this->data['wildcardDnsTimeoutMinutes'] === '')
        {
            $this->data['wildcardDnsTimeoutMinutes'] = AutoInstallHelpers::DEFAULT_WILDCARD_DNS_TIMEOUT_MINUTES;
        }

        if(!isset($this->data['autoReissueDaysBeforeExpire']))
        {
            $this->data['autoReissueDaysBeforeExpire'] = AutoInstallHelpers::DEFAULT_AUTO_REISSUE_DAYS_BEFORE_EXPIRE;
        }

        $this->availableValues['country']                               = Helpers::getCountryOptions();
        // empty = each client's own country (the first country of the list used to be saved without being chosen)
        // "client" (the client's own country) is a real value: the dropdown drops an option with an empty value, which
        // left Afghanistan, the first country, selected and saved
        $this->availableValues['defaultCsrCountry']                     = [self::CSR_COUNTRY_CLIENT => "The client's own country"] + Helpers::getCountryOptions();
        $this->data['defaultCsrCountry']                                = preg_match('/^[A-Z]{2}$/', strtoupper(trim((string)($this->data['defaultCsrCountry'] ?? '')))) ? $this->data['defaultCsrCountry'] : self::CSR_COUNTRY_CLIENT;
        $this->availableValues['additionalDaysForRenewalOrder']         = Helpers::get30DaysOptions();
        $reissueDays = [1, 3, 7, 10, 21, 30];
        $current     = (int)$this->data['autoReissueDaysBeforeExpire'];
        if(!in_array($current, $reissueDays, true))
        {
            $reissueDays[] = $current; // a value saved before the shorter list stays selectable
            sort($reissueDays);
        }
        $this->availableValues['autoReissueDaysBeforeExpire']           = array_combine($reissueDays, $reissueDays);
        $this->availableValues['recurringDaysBeforeExpirySubscription'] = $this->dayOptions(RenewalPolicy::LEAD_OPTIONS, 'recurringDaysBeforeExpirySubscription');
        $this->availableValues['recurringPaymentTermSubscription']      = $this->dayOptions(RenewalPolicy::TERM_OPTIONS, 'recurringPaymentTermSubscription');
        // built first and assigned whole: availableValues is not a plain array, a nested write would be lost
        $pendingWait = [];
        foreach(RenewalPolicy::PENDING_WAIT_OPTIONS as $days)
        {
            $pendingWait[(string)$days] = $this->translate('pendingWait_' . $days);
        }
        $this->availableValues['pendingPaymentWaitSubscription']        = $pendingWait;
        $this->availableValues['sanUpgradePricing']                     = [
            \ModulesGarden\TSSGGSModule\App\Libs\Billing\SanUpgrade::PRICING_FULL     => $this->translate('sanUpgradePricing_full'),
            \ModulesGarden\TSSGGSModule\App\Libs\Billing\SanUpgrade::PRICING_PRORATED => $this->translate('sanUpgradePricing_prorated'),
        ];
        $this->availableValues['subscriptionSlotPricing']               = [
            \ModulesGarden\TSSGGSModule\App\Libs\Subscriptions\SubscriptionBilling::SLOT_PRICING_PRORATED => $this->translate('subscriptionSlotPricing_prorated'),
            \ModulesGarden\TSSGGSModule\App\Libs\Subscriptions\SubscriptionBilling::SLOT_PRICING_FULL     => $this->translate('subscriptionSlotPricing_full'),
        ];
        $this->availableValues['recurringDaysBeforeExpiry']             = $this->dayOptions(RenewalPolicy::LEAD_OPTIONS, 'recurringDaysBeforeExpiry');
        $this->availableValues['recurringPaymentTerm']                  = $this->dayOptions(RenewalPolicy::TERM_OPTIONS, 'recurringPaymentTerm');
        $this->availableValues['oneTimeDaysBeforeExpiry']               = Helpers::getDaysOptions();

        // the SSL certificates page also shows settings of the general store (downloads, messages, due dates, ordering)
        foreach(GeneralSettings::all() as $key => $value)
        {
            $this->data[$key] = in_array($key, GeneralSettingsProvider::SWITCHES, true) ? GeneralSettings::enabled($key) : $value;
        }
        // issued SSL message of each family: what applies, and each language box with the text the client would get
        foreach(array_keys(IssuedMessage::FAMILIES) as $family)
        {
            $this->data[IssuedMessage::SWITCH_PREFIX . $family] = IssuedMessage::enabled($family);
            foreach(IssuedMessage::languages() as $language)
            {
                $stored = IssuedMessage::stored($family, $language);
                $this->data[IssuedMessage::textKey($family, $language)] = $stored !== '' ? $stored : IssuedMessage::builtin($family, $language);
            }
        }
        // what applies: an install that never saved it keeps the choice it made for SSL before 1.5.0
        $this->data['renewReuseOrderDetails'] = RenewalPolicy::reuseDetails();
        // as a string: the dropdown takes a numeric 0 for "nothing chosen"
        $this->data['pendingPaymentWaitSubscription'] = (string)RenewalPolicy::pendingWait(RenewalPolicy::SUBSCRIPTION);

        // the module template, the administrator's own Product/Service templates (CertificateService::certificateTemplates)
        // and the saved one, so a choice made before stays visible
        $templates = ['' => $this->translate('moduleTemplate')];
        foreach(\ModulesGarden\TSSGGSModule\App\Libs\Certificates\CertificateService::certificateTemplates() as $name)
        {
            $templates[$name] = $name;
        }
        $saved = trim((string)\ModulesGarden\TSSGGSModule\App\Libs\Support\GeneralSettings::get('certificateEmailTemplate'));
        if($saved !== '' && !isset($templates[$saved]))
        {
            $templates[$saved] = $saved;
        }
        $this->availableValues['certificateEmailTemplate'] = $templates;
    }

    public function update()
    {
        Validator::validate($this->formData->toArray(), [
                                                          'wildcardDnsTimeoutMinutes' => [
                                                              function(string $attribute, $value, \Closure $fail) {
                                                                  if(!preg_match('/^\d+$/', trim((string)$value)) || (int)$value < 1)
                                                                  {
                                                                      $fail($this->translate('wildcardDnsTimeoutMinutesInvalidValue', [
                                                                          'field' => $attribute
                                                                      ]));
                                                                  }
                                                              }
                                                          ],
                                                          'recurringSendExpirationNotifications' => [
                                                              function(string $attribute, $value, \Closure $fail) {
                                                                  $parts = explode(",", $value);
                                                                  foreach($parts as $part)
                                                                  {
                                                                      $part = (int)trim($part);
                                                                      if($part < 0 || $part > 30)
                                                                      {
                                                                          $fail($this->translate('recurringSendExpirationNotificationsInvalidValue', [
                                                                              'field' => $attribute
                                                                          ]));
                                                                      }
                                                                  }
                                                              }
                                                          ],
                                                          'oneTimeSendExpirationNotifications'   => [
                                                              function(string $attribute, $value, \Closure $fail) {
                                                                  $parts = explode(",", $value);
                                                                  foreach($parts as $part)
                                                                  {
                                                                      $part = (int)trim($part);
                                                                      if($part < 0 || $part > 30)
                                                                      {
                                                                          $fail($this->translate('oneTimeSendExpirationNotificationsInvalidValue', [
                                                                              'field' => $attribute
                                                                          ]));
                                                                      }
                                                                  }
                                                              }
                                                          ],
                                                          'recurringSendExpirationNotificationsSubscription'   => [
                                                              function(string $attribute, $value, \Closure $fail) {
                                                                  $parts = explode(",", $value);
                                                                  foreach($parts as $part)
                                                                  {
                                                                      $part = (int)trim($part);
                                                                      if($part < 0 || $part > 30)
                                                                      {
                                                                          $fail($this->translate('recurringSendExpirationNotificationsSubscription', [
                                                                              'field' => $attribute
                                                                          ]));
                                                                      }
                                                                  }
                                                              }
                                                          ]

                                                      ]
        );

        $data = $this->formData->toArray();
        // stored empty: the client area then uses the client's own country
        if(array_key_exists('defaultCsrCountry', $data) && $data['defaultCsrCountry'] === self::CSR_COUNTRY_CLIENT)
        {
            $data['defaultCsrCountry'] = '';
        }

        // fields of the general store (downloads, messages, due dates ...) go there, the rest into the SSL settings; both
        // are merged into what is stored, so a page that shows only part of the settings keeps the others
        $general = GeneralSettings::pick($data);
        // an issued message box left with the built-in text stores nothing: the install follows the module's wording
        foreach($general as $key => $value)
        {
            // WHMCS posts the fields HTML-encoded; the client messages allow HTML and are shown as written
            if(in_array($key, \ModulesGarden\TSSGGSModule\App\Libs\Support\MessageHtml::KEYS, true) || IssuedMessage::isTextKey($key))
            {
                $general[$key] = $value = \ModulesGarden\TSSGGSModule\App\Libs\Support\MessageHtml::posted($value);
            }
            if(IssuedMessage::isTextKey($key) && preg_match('/^issuedMessage_([a-z]+)_(.+)$/', $key, $match))
            {
                $general[$key] = IssuedMessage::toStore($match[1], $match[2], $value);
            }
        }
        if($general)
        {
            GeneralSettingsProvider::saveGeneral($general);
        }
        (new AddonModuleRepository)->saveSslSettings(array_diff_key($data, $general));
        // the dashboard's "Check renewal settings" step
        NextSteps::markDone(NextSteps::STEP_RENEWALS);
    }

    /**
     * Day options of a dropdown; a value saved that is not in the list stays selectable
     */
    protected function dayOptions(array $days, string $key): array
    {
        $current = isset($this->data[$key]) && is_numeric($this->data[$key]) ? (int)$this->data[$key] : null;
        if($current !== null && !in_array($current, $days, true))
        {
            $days[] = $current;
            sort($days);
        }

        return array_combine($days, $days);
    }
}
