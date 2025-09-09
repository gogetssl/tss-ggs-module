<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\KanbanBoard\Providers;

use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;

class BoardProvider extends CrudProvider
{
    public function update()
    {
        //print_r($this->formData);
        throw new \Exception('Not implemented');
    }
}