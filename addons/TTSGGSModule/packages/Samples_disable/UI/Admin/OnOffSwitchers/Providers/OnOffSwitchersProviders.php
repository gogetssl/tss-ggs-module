<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\OnOffSwitchers\Providers;

use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TTSGGSModule\Packages\ModuleSettings\Support\Facades\ModuleSettings;

class OnOffSwitchersProviders extends CrudProvider
{
    public function read()
    {
        $this->data['switcherOnOff'] = ModuleSettings::get('OnOffSwitchers.switcherOnOff');
    }

    public function update()
    {
        ModuleSettings::save(['OnOffSwitchers.switcherOnOff' => $this->formData->get('switcherOnOff'),]);
    }
}