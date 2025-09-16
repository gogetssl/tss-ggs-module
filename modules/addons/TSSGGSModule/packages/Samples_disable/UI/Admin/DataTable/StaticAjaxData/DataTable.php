<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\StaticAjaxData;

use ModulesGarden\TSSGGSModule\Components\Button\ButtonPrimary;
use ModulesGarden\TSSGGSModule\Components\DataTable\Column;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ModalOpen;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\PassAjaxData;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\Reload;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\ResponseInterface;
use ModulesGarden\TSSGGSModule\Core\DataProviders\ArrayDataProvider;
use ModulesGarden\TSSGGSModule\Core\Support\Arr;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;

class DataTable extends \ModulesGarden\TSSGGSModule\Components\DataTable\DataTable implements AjaxComponentInterface, AdminAreaInterface
{
    protected string $cid = 'my-table';

    public function loadHtml(): void
    {
        $this->addColumn((new Column('id')));

        $this->addToBurgerToolbar((new ButtonPrimary())->onClick(new ModalOpen(new Modal())));
    }

    public function loadData(): void
    {
        $data  = Arr::get(Request::get('ajaxData'), 'records', []);
        $toAdd = Arr::get(Request::get('ajaxData'), 'recordToAdd', null);

        if ($toAdd)
        {
            $data[] = [
                'id' => $toAdd
            ];
        }

        $this->setDataProvider((new ArrayDataProvider($data)));
        $this->setAjaxData(['records' => $data, 'recordToAdd' => null]);
    }
}