<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ElementsListComponent\ElementsLists;

use ModulesGarden\TTSGGSModule\Components\ElementsList\ElementsList as ElementsListComponent;
use ModulesGarden\TTSGGSModule\Components\Text\Text;
use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\DataProviders\QueryDataProvider;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Client;

class ElementsList extends ElementsListComponent implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadData(): void
    {
        $clients = Client::select('tblclients.id', 'tblclients.firstname', 'lastname', 'taxexempt', 'companyname');

        $dataProv = new QueryDataProvider($clients);
        $dataProv->setColumns([
            (new \ModulesGarden\TTSGGSModule\Core\DataProviders\Column('id'))->setSearchable(true),
            (new \ModulesGarden\TTSGGSModule\Core\DataProviders\Column('firstname'))->setSearchable(true),
        ]);
        $dataProv->setDefaultSorting('tblclients.id', 'DESC');
        $this->setDataProvider($dataProv);
        $this->setAjaxData(['rand' . rand(0, 100) => 1]);
    }

    protected function buildElement($record): AbstractComponent
    {
        return (new Text())->setText($record->firstname);
    }
}