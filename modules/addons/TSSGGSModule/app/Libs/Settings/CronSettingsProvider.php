<?php

namespace TSSGGSModule\App\Libs\Settings;

use TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;


class CronSettingsProvider extends Provider
{
    const TEXTS = 'admin.settings.index.providers.cron_settings_provider';

    public function read()
    {
        $moduleRepository    = new AddonModuleRepository();
        $moduleConfiguration = $moduleRepository->getModuleConfiguration();

        foreach((array)($moduleConfiguration['cronSettings'] ?? []) as $key => $value)
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
