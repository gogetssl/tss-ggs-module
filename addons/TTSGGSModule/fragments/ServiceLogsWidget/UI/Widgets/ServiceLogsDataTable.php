<?php

namespace ModulesGarden\TTSGGSModule\Fragments\ServiceLogsWidget\UI\Widgets;

use ModulesGarden\TTSGGSModule\Components\DataTable\Column;
use ModulesGarden\TTSGGSModule\Components\DataTable\DataTable;
use ModulesGarden\TTSGGSModule\Components\VisibilityWrapper\VisibilityWrapper;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ClientAreaInterface;
use ModulesGarden\TTSGGSModule\Core\DataProviders\AbstractRecordsListDataProvider;
use ModulesGarden\TTSGGSModule\Core\DataProviders\QueryDataProvider;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Config;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Params;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Buttons\DeleteLogButton;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Buttons\MassDeleteButton;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Buttons\ShowDataButton;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Others\TypeLabel;
use WHMCS\Database\Capsule as DB;
use ModulesGarden\TTSGGSModule\Packages\Logs\Models\Logs;
use function ModulesGarden\TTSGGSModule\Core\Helper\isAdmin;

class ServiceLogsDataTable extends DataTable implements AdminAreaInterface, ClientAreaInterface, AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('service_logs'));

        $this->addColumn((new Column('id'))
            ->setTitle($this->translate('id'))
            ->setSortable()
            ->setSearchable(true, Column::TYPE_INT));

        $this->addColumn((new Column('message'))
            ->setTitle($this->translate('message')));

        $this->addColumn((new Column('type'))
            ->setTitle($this->translate('type'))
            ->setSortable()
            ->setSearchable(true, Column::TYPE_STRING));

        $this->addColumn((new Column('date'))
            ->setTitle($this->translate('date'))
            ->setSortable()
            ->setSearchable(true, Column::TYPE_DATE));

        if (isAdmin())
        {
            $this->addActionButton((new VisibilityWrapper(new ShowDataButton()))->disableWhen("isDataEnabled", false));
        }

        if (isAdmin() && Config::get('logs.delete_logs.enabled', true))
        {
            $this->addActionButton(new DeleteLogButton());
            $this->addMassActionButton(new MassDeleteButton());
        }
    }

    public function loadData(): void
    {
        $serviceId = Params::get('serviceid', Request::get('id', 0 ));
        $query = Logs::select('id', 'type', 'date', 'data', 'message', DB::raw("(data IS NOT NULL) AND (data != '[]') as isDataEnabled"))
            ->whereRaw("JSON_VALID(data)")
            ->whereRaw("JSON_EXTRACT(data, '$.service') = ?", $serviceId);

        $dataProv = new QueryDataProvider($query);
        $dataProv->setDefaultSorting('id', AbstractRecordsListDataProvider::SORT_DESC);
        $this->setDataProvider($dataProv);
    }

    protected function parseDataSetRecords(): void
    {
        $this->dataSet->setFieldModifier('type', function($fieldName, $row, $fieldValue) {
            return TypeLabel::create($fieldValue);
        });

        $this->dataSet->setFieldModifier('message', function($fieldName, $row, $fieldValue) {
            $replace = [];

            foreach ($row['data'] as $key => $val)
            {
                // check that the value can be cast to string
                if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString')))
                {
                    $replace[':' . $key] = $val;
                }
            }

            return strtr($this->translate($fieldValue, $replace, ['packages.logs.pages.logs_data_table']), $replace);
        });

        $this->dataSet->modifyRecords();
    }
}