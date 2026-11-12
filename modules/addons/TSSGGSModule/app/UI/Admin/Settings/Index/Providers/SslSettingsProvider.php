<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers;

use ModulesGarden\TSSGGSModule\App\Libs\AutoInstallHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\GeneralSettings;
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

        if(!isset($this->data['recurringDaysBeforeExpirySubscription']))
        {
            $this->data['recurringDaysBeforeExpirySubscription'] = 30;
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
        $this->availableValues['recurringDaysBeforeExpirySubscription'] = Helpers::getDaysOptions(true);
        $this->availableValues['recurringDaysBeforeExpiry']             = Helpers::getDaysOptions();
        $this->availableValues['oneTimeDaysBeforeExpiry']               = Helpers::getDaysOptions();

        // the SSL certificates page also shows settings of the general store (downloads, messages, due dates, ordering)
        foreach(GeneralSettings::all() as $key => $value)
        {
            $this->data[$key] = in_array($key, GeneralSettingsProvider::SWITCHES, true) ? GeneralSettings::enabled($key) : $value;
        }
        $offsets = ['0' => '0', '3' => '3', '7' => '7', '15' => '15', '30' => '30'];
        $this->availableValues['dueDateOffsetRegular']      = $offsets;
        $this->availableValues['dueDateOffsetSubscription'] = $offsets;

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
}