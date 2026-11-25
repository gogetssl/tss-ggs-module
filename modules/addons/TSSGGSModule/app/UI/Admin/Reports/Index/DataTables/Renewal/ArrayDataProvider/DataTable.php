<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\Renewal\ArrayDataProvider;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\Shared\StatusLabel;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\Renewal\Forms\DownloadForm;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\Shared\RecordsProvider;
use ModulesGarden\TSSGGSModule\Components\DataTable\Column;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\DataProviders\ArrayDataProvider;

use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;

class DataTable extends \ModulesGarden\TSSGGSModule\Components\DataTable\DataTable implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $this->setId('renewalDataTable');

        $this->addColumn((new Column('date'))
                             ->setTitle($this->translate('date'))
                             ->setSortable()
                             ->setSearchable(true))
             ->addColumn((new Column('storeId'))
                             ->setTitle($this->translate('storeId'))
                             ->setSortable(true)
                             ->setSearchable(true))
             ->addColumn((new Column('clientDetails'))
                             ->setTitle($this->translate('clientDetails'))
                             ->setSortable(true)
                             ->setSearchable(true))
             ->addColumn((new Column('productDetails'))
                             ->setTitle($this->translate('productDetails'))
                             ->setSortable(true)
                             ->setSearchable(true))
             ->addColumn((new Column('type'))
                             ->setTitle($this->translate('type'))
                             ->setSortable(true)
                             ->setSearchable(true))
             ->addColumn((new Column('brand'))
                             ->setTitle($this->translate('brand'))
                             ->setSortable(true)
                             ->setSearchable(true))
             ->addColumn((new Column('status'))
                             ->setTitle($this->translate('status'))
                             ->setSortable(true)
                             ->setSearchable(true))
             ->addColumn((new Column('issueDate'))
                             ->setTitle($this->translate('issueDate'))
                             ->setSortable(true)
                             ->setSearchable(true))
             ->addColumn((new Column('expirationDate'))
                             ->setTitle($this->translate('expirationDate'))
                             ->setSortable(true)
                             ->setSearchable(true));

        $this->setRecordsPerPageOptions([10, 25]);
        $this->addToToolbar(new DownloadForm());
    }

    public function loadData(): void
    {
        $filters         = Request::get('ajaxData');//custom filter values
        $recordsProvider = new RecordsProvider();
        $rows            = $recordsProvider->getRecords($filters);

        $dataProv = new ArrayDataProvider($rows);
        $dataProv->setDefaultSorting('date', 'DESC');
        $this->setDataProvider($dataProv);
    }

    protected function parseDataSetRecords(): void
    {
        $this->dataSet->setFieldModifier('status', function($fieldName, $row, $fieldValue) {
            return StatusLabel::order($fieldValue);
        });

        $this->dataSet->modifyRecords();
    }
}
