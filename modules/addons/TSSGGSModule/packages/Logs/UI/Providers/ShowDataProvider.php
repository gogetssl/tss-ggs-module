<?php

namespace ModulesGarden\TSSGGSModule\Packages\Logs\UI\Providers;

use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TSSGGSModule\Packages\Logs\Models\Logs;

class ShowDataProvider extends CrudProvider
{
    public function read()
    {
        $this->data->createFrom(Logs::where('id', $this->formData['id'])->select('data')->first()->toArray());
    }
}
