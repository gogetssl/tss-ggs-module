<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\OnOffSwitchers\Providers;

use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TSSGGSModule\Packages\ModuleSettings\Support\Facades\ModuleSettings;

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