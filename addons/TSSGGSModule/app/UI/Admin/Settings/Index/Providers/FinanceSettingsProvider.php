<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers;

use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Validator;


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