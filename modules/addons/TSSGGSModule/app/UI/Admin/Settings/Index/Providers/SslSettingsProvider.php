<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers;

use ModulesGarden\TSSGGSModule\App\Libs\AutoInstallHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\GeneralSettings;
use ModulesGarden\TSSGGSModule\App\Libs\RenewalPolicy;
use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\NextStepsWidget;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Validator;


class SslSettingsProvider extends CrudProvider
{
    public function read()
    {
        $moduleRepository    = new AddonModuleRepository();
        $moduleConfiguration = $moduleRepository->getModuleConfiguration();

        foreach($moduleConfiguration['sslSettings'] as $key => $value)
        {
            $this->data[$key] = $value;
        }

        // renewal invoices (App\Libs\RenewalPolicy): created 1 to 30 days before the order end, due a payment term
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
        if(empty($this->data['subscriptionSlotPricing']))
        {
            $this->data['subscriptionSlotPricing'] = \ModulesGarden\TSSGGSModule\App\Libs\SubscriptionBilling::SLOT_PRICING_PRORATED;
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
        $this->availableValues['defaultCsrCountry']                     = ['' => "The client's own country"] + Helpers::getCountryOptions();
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
        // as a string: the dropdown takes a numeric 0 for "nothing chosen"
        $this->data['pendingPaymentWaitSubscription']                   = (string)RenewalPolicy::pendingWait(RenewalPolicy::SUBSCRIPTION);
        $this->availableValues['pendingPaymentWaitSubscription']        = [];
        foreach(RenewalPolicy::PENDING_WAIT_OPTIONS as $days)
        {
            $this->availableValues['pendingPaymentWaitSubscription'][(string)$days] = $this->translate('pendingWait_' . $days);
        }
        $this->availableValues['subscriptionSlotPricing']               = [
            \ModulesGarden\TSSGGSModule\App\Libs\SubscriptionBilling::SLOT_PRICING_PRORATED => $this->translate('subscriptionSlotPricing_prorated'),
            \ModulesGarden\TSSGGSModule\App\Libs\SubscriptionBilling::SLOT_PRICING_FULL     => $this->translate('subscriptionSlotPricing_full'),
        ];
        $this->availableValues['recurringDaysBeforeExpiry']             = $this->dayOptions(RenewalPolicy::LEAD_OPTIONS, 'recurringDaysBeforeExpiry');
        $this->availableValues['recurringPaymentTerm']                  = $this->dayOptions(RenewalPolicy::TERM_OPTIONS, 'recurringPaymentTerm');
        $this->availableValues['oneTimeDaysBeforeExpiry']               = Helpers::getDaysOptions();

        // the SSL certificates page also shows settings of the general store (downloads, messages, due dates, ordering)
        foreach(GeneralSettings::all() as $key => $value)
        {
            $this->data[$key] = in_array($key, GeneralSettingsProvider::SWITCHES, true) ? GeneralSettings::enabled($key) : $value;
        }
        // what applies: an install that never saved it keeps the choice it made for SSL before 1.5.0
        $this->data['renewReuseOrderDetails'] = RenewalPolicy::reuseDetails();

        $templates = ['' => $this->translate('moduleTemplate')];
        foreach(\WHMCS\Database\Capsule::table('tblemailtemplates')->where('type', 'general')->where('language', '')->orderBy('name')->pluck('name') as $name)
        {
            $templates[$name] = $name;
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

        // fields of the general store (downloads, messages, due dates ...) go there, the rest into the SSL settings; both
        // are merged into what is stored, so a page that shows only part of the settings keeps the others
        $general = array_intersect_key($data, GeneralSettings::DEFAULTS);
        if($general)
        {
            GeneralSettingsProvider::saveGeneral($general);
        }
        (new AddonModuleRepository)->saveSslSettings(array_diff_key($data, GeneralSettings::DEFAULTS));
        // the dashboard's "Check renewal settings" step
        NextStepsWidget::markDone(NextStepsWidget::STEP_RENEWALS);
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
