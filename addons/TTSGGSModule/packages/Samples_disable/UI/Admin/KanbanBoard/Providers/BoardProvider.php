<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\KanbanBoard\Providers;

use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;

class BoardProvider extends CrudProvider
{
    public function update()
    {
        //print_r($this->formData);
        throw new \Exception('Not implemented');
    }
}