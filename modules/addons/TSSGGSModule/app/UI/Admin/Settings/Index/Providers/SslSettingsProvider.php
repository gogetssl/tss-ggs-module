<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers;

use ModulesGarden\TSSGGSModule\App\Libs\AutoInstallHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
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
        $this->availableValues['defaultCsrCountry']                     = Helpers::getCountryOptions();
        $this->availableValues['additionalDaysForRenewalOrder']         = Helpers::get30DaysOptions();
        $this->availableValues['autoReissueDaysBeforeExpire']           = Helpers::get30DaysOptions();
        $this->availableValues['recurringDaysBeforeExpirySubscription'] = Helpers::getDaysOptions(true);
        $this->availableValues['recurringDaysBeforeExpiry']             = Helpers::getDaysOptions();
        $this->availableValues['oneTimeDaysBeforeExpiry']               = Helpers::getDaysOptions();
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
        (new AddonModuleRepository)->saveSslSettings($data);
    }
}