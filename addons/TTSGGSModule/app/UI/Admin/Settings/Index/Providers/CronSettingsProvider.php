<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Providers;

use ModulesGarden\TTSGGSModule\App\Libs\Helpers;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Validator;


class CronSettingsProvider extends CrudProvider
{
    public function read()
    {
        $moduleRepository    = new AddonModuleRepository();
        $moduleConfiguration = $moduleRepository->getModuleConfiguration();

        foreach($moduleConfiguration['cronSettings'] as $key => $value)
        {
            $this->data[$key] = $value;
        }
    }

    public function update()
    {
        $data = $this->formData->toArray();
        (new AddonModuleRepository)->saveCronSettings($data);
    }
}