<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Providers;

use ModulesGarden\TTSGGSModule\App\Libs\Helpers;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Validator;


class FinanceSettingsProvider extends CrudProvider
{
    public function read()
    {
        $moduleRepository    = new AddonModuleRepository();
        $moduleConfiguration = $moduleRepository->getModuleConfiguration();

        foreach($moduleConfiguration['financeSettings'] as $key => $value)
        {
            $this->data[$key] = $value;
        }

        $this->availableValues['currency'] = Helpers::getCurrencyOptions();
    }

    public function update()
    {
        $data = $this->formData->toArray();
        (new AddonModuleRepository)->saveFinanceSettings($data);
    }
}