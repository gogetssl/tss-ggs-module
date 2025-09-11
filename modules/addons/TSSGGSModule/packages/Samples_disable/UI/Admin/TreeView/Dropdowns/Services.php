<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TreeView\Dropdowns;

use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Client;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Hosting;
use WHMCS\Database\Capsule as DB;


class Services extends Dropdown implements AdminAreaInterface, AjaxComponentInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('service_id');
        $this->setName('service_id');
    }

    public function loadHtml(): void
    {
        //$this->setName('clients');
//        $this->setAjaxOnLoad();
//        $this->setAjaxSearch();
        $this->setMultiple();

    }

    public function loadData(): void
    {
        $clients = Hosting::select(DB::raw('id as value'), DB::raw('domain as name'))->where('userid', (int) Request::get('ajaxData')['value'] ?? null)
            ->get()
            ->toArray();

        $this->setOptions($clients);
    }
}
