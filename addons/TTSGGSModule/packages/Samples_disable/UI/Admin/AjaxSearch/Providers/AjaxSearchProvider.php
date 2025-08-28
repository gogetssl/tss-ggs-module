<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\AjaxSearch\Providers;

use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Client;
use ModulesGarden\TTSGGSModule\Packages\ModuleSettings\Support\Facades\ModuleSettings;

class AjaxSearchProvider extends CrudProvider
{
    public function read()
    {
        $this->data = $this->formData;

        $this->data->set('clientsField', ModuleSettings::get('AjaxSearch.clients_field'));

        $clients = Client::get();

        foreach ($clients as $entity)
        {
            $clientName = sprintf('#%s %s %s (%s)', $entity->id, $entity->firstname, $entity->lastname, $entity->email);
            $this->availableValues->set('clientsField.' . $entity->id, $clientName);
        }
    }

    public function update()
    {
        ModuleSettings::save(['AjaxSearch.clients_field' => $this->formData->get('clientsField'),]);
    }
}