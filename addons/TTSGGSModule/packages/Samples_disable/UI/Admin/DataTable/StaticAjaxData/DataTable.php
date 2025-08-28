<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\StaticAjaxData;

use ModulesGarden\TTSGGSModule\Components\Button\ButtonPrimary;
use ModulesGarden\TTSGGSModule\Components\DataTable\Column;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ModalOpen;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\PassAjaxData;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\Reload;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\ResponseInterface;
use ModulesGarden\TTSGGSModule\Core\DataProviders\ArrayDataProvider;
use ModulesGarden\TTSGGSModule\Core\Support\Arr;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;

class DataTable extends \ModulesGarden\TTSGGSModule\Components\DataTable\DataTable implements AjaxComponentInterface, AdminAreaInterface
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