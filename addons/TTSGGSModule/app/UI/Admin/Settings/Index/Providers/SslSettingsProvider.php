<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Providers;

use ModulesGarden\TTSGGSModule\App\Libs\Helpers;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Validator;


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

        $this->availableValues['country']                       = Helpers::getCountryOptions();
        $this->availableValues['defaultCsrCountry']             = Helpers::getCountryOptions();
        $this->availableValues['additionalDaysForRenewalOrder'] = Helpers::get30DaysOptions();
        $this->availableValues['recurringDaysBeforeExpiry']     = Helpers::getDaysOptions();
        $this->availableValues['oneTimeDaysBeforeExpiry']       = Helpers::getDaysOptions();
    }

    public function update()
    {
        Validator::validate($this->formData->toArray(), [
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
                                                          ]
                                                      ]
        );

        $data = $this->formData->toArray();
        (new AddonModuleRepository)->saveSslSettings($data);
    }
}