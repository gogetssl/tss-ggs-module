<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\CollapsableBox\Providers;

use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TSSGGSModule\Packages\ModuleSettings\Support\Facades\ModuleSettings;

class CollapsableBoxProvider extends CrudProvider
{
    public function read()
    {
        $this->data['switcher1'] = true;
        $this->data['switcher2'] = false;
        $this->data['switcher3'] = false;
        $this->data['switcher4'] = true;

        $this->data['someInput']  = "Some Text";
        $this->data['someInput2'] = "Some Inny Text";
        $this->data['someInput3'] = "To i tak nie działa";
        $this->data['someInput4'] = "Chłooooopie";
    }

    public function update()
    {
    }
}