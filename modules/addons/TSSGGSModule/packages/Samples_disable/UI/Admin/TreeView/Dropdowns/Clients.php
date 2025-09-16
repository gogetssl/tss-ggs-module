<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TreeView\Dropdowns;

use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\PassAjaxData;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Client;
use WHMCS\Database\Capsule as DB;


class Clients extends Dropdown implements AdminAreaInterface, AjaxComponentInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('client_id');
        $this->setName('client_id');
    }

    public function loadHtml(): void
    {
        //$this->setName('clients');
        $this->setAjaxOnLoad();
        $this->setAjaxSearch();
        $this->onChange(new PassAjaxData('service_id'));
        $this->onChange(new ReloadById('service_id'));
    }

    public function loadData(): void
    {
        $clients = Client::select(DB::raw('id as value'), DB::raw('CONCAT(firstname, " ", lastname) as name'))->where('firstname', 'LIKE', '%' . Request::get('query') . '%')
            ->get()
            ->toArray();

        $this->setOptions($clients);
    }
}
