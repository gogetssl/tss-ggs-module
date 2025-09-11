<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\HintsBox\Providers;

use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TSSGGSModule\Packages\ModuleSettings\Support\Facades\ModuleSettings;

class SettingsProvider extends CrudProvider
{
    public function read()
    {
        $this->data['hideGuide'] = ModuleSettings::get('HintsBox.hide_guide');
    }

    public function update()
    {
        ModuleSettings::save(['HintsBox.hide_guide' => $this->formData->get('hideGuide'),]);
    }
}