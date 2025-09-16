<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DynamicTabs\Providers;

use ModulesGarden\TSSGGSModule\Core\Components\Actions\ModalClose;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\Reload;
use ModulesGarden\TSSGGSModule\Core\Components\Response\Response;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TSSGGSModule\Packages\ModuleSettings\Support\Facades\ModuleSettings;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DynamicTabs\Pages\DynamicTabs;

class EditTabsProvider extends CrudProvider
{
    public function read()
    {
        $this->availableValues['tabsNames'] = $this->getAvailableTabs();
        $this->data->set('tabsNames', json_decode(ModuleSettings::get('samples.dynamicTabsNames', [])));
    }

    public function update()
    {
        ModuleSettings::save(['samples.dynamicTabsNames' => json_encode($this->formData->get('tabsNames', []))]);

        return (new Response())->setSuccess("Tab Edited Successfully")
            ->setActions([new Reload(new DynamicTabs()), new ModalClose()]);
    }

    public function getAvailableTabs():array
    {
        $availableTabs = [];

        for ($i = 1 ; $i < 10 ; $i++)
        {
            $availableTabs["Tab" . $i] = "Tab" . $i;
        }

        return $availableTabs;
    }
}