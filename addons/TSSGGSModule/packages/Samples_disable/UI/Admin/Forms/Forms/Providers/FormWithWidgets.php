<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Home\Forms\Providers;

class FormWithWidgets extends \ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider
{
    public function create()
    {
        \ModulesGarden\TSSGGSModule\Core\validator()->validate(\ModulesGarden\TSSGGSModule\Core\Support\Facades\Request::getAll(), [
            'text'     => '',
            'dropdown' => 'required',
        ]);
        // TODO: Implement create() method.
    }

    public function delete()
    {
        // TODO: Implement delete() method.
    }

    public function read()
    {
        // TODO: Implement read() method.
    }

    public function update()
    {
        // TODO: Implement update() method.
    }
}
