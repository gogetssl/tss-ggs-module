<?php

namespace ModulesGarden\TTSGGSModule\Packages\Logs\UI\Providers;

use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TTSGGSModule\Packages\Logs\Models\Logs;

class ShowDataProvider extends CrudProvider
{
    public function read()
    {
        $this->data->createFrom(Logs::where('id', $this->formData['id'])->select('data')->first()->toArray());
    }
}
