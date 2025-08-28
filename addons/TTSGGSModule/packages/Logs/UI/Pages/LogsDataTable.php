<?php

namespace ModulesGarden\TTSGGSModule\Packages\Logs\UI\Pages;

use ModulesGarden\TTSGGSModule\Components\DataTable\Column;
use ModulesGarden\TTSGGSModule\Components\DataTable\DataTable;
use ModulesGarden\TTSGGSModule\Components\DropdownMenu\DropdownMenu;
use ModulesGarden\TTSGGSModule\Components\VisibilityWrapper\VisibilityWrapper;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\DataProviders\AbstractRecordsListDataProvider;
use ModulesGarden\TTSGGSModule\Core\DataProviders\QueryDataProvider;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Config;
use ModulesGarden\TTSGGSModule\Packages\Logs\Models\Logs;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Buttons\DeleteLogButton;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Buttons\EditConfiguration;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Buttons\ExportCsvButton;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Buttons\MassDeleteButton;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Buttons\MenuDeleteButton;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Buttons\ShowDataButton;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Formatters\RelatedItem;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Others\TypeLabel;
use WHMCS\Database\Capsule as DB;

class LogsDataTable extends DataTable implements AdminAreaInterface, AjaxComponentInterface
{
    protected string $cid = 'LogsDataTable';

    public function loadHtml(): void
    {
        $this->addColumn((new Column('id'))
            ->setTitle($this->translate('id'))
            ->setSortable()
            ->setSearchable(true, Column::TYPE_INT));

        if (Config::get('logs.related_item.show', true))
        {
            $this->addColumn((new Column('related_item'))
                ->setTitle($this->translate('related_item')));
        }
        $this->addColumn((new Column('message'))
            ->setTitle($this->translate('message'))
            ->setSortable()
            ->setSearchable(true, Column::TYPE_STRING));
        $this->addColumn((new Column('type'))
            ->setTitle($this->translate('type'))
            ->setSortable()
            ->setSearchable(true, Column::TYPE_STRING));
        $this->addColumn((new Column('date'))
            ->setTitle($this->translate('date'))
            ->setSortable()
            ->setSearchable(true, Column::TYPE_DATE));

        $this->addActionButton((new VisibilityWrapper(new ShowDataButton()))->disableWhen("isDataEnabled", false));

        $burger = new DropdownMenu();
        $burger->addItem(new EditConfiguration());
        $burger->addItem(new ExportCsvButton());

        if (Config::get('logs.delete_logs.enabled', true))
        {
            $this->addActionButton(new DeleteLogButton());
            $this->addMassActionButton(new MassDeleteButton());
            $burger->addItem(new MenuDeleteButton());
        }

        $this->addToToolbar($burger);
    }

    public function loadData(): void
    {
        $dataProv = new QueryDataProvider(
            Logs::select('id', 'type', 'date', 'data', 'message', DB::raw("(data IS NOT NULL) AND (data != '[]') as isDataEnabled"))
        );

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

            return strtr($this->translate($fieldValue), $replace);
        });

        if (Config::get('logs.related_item.show', true))
        {
            $this->dataSet->setFieldModifier('related_item', Config::get('logs.related_item.formatter', new RelatedItem()));
        }

        $this->dataSet->modifyRecords();
    }
}
