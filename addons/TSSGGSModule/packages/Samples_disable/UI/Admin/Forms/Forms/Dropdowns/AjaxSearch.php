<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Forms\Forms\Dropdowns;

use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;

class AjaxSearch extends Dropdown
{
    public function loadHtml(): void
    {
        $this->setAjaxSearch();
        $this->setName('Ajax Search');
    }

    public function loadData(): void
    {
        $searchFor = Request::get('query');

        $this->setOptions([
            [
                'value' => 1,
                'name'  => 'XXXX',
            ],
            [
                'value' => 2,
                'name'  => 'YYYY',
            ],
        ]);
    }
}