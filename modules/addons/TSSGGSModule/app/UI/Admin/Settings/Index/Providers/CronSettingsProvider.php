<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers;

use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;


class CronSettingsProvider extends CrudProvider
{
    public function read()
    {
        $moduleRepository    = new AddonModuleRepository();
        $moduleConfiguration = $moduleRepository->getModuleConfiguration();

        foreach($moduleConfiguration['cronSettings'] as $key => $value)
        {
            $this->data[$key] = $moduleRepository->cronFrequency($key, $value);
        }
    }

    public function update()
    {
        $data = $this->formData->toArray();
        (new AddonModuleRepository)->saveCronSettings($data);
    }
}
