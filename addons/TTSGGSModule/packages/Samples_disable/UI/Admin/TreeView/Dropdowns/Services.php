<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TreeView\Dropdowns;

use ModulesGarden\TTSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Client;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Hosting;
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
