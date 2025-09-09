<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\ProfitLoss\ArrayDataProvider;


use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\ProfitLoss\Forms\DownloadForm;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\Shared\RecordsProvider;
use ModulesGarden\TSSGGSModule\Components\DataTable\Column;
use ModulesGarden\TSSGGSModule\Components\Label\LabelDanger;
use ModulesGarden\TSSGGSModule\Components\Label\LabelSuccess;
use ModulesGarden\TSSGGSModule\Components\Label\LabelWarning;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\DataProviders\ArrayDataProvider;

use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;
use WHMCS\User\Client;

class DataTable extends \ModulesGarden\TSSGGSModule\Components\DataTable\DataTable implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $this->setId('profitLossDataTable');

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
             ->addColumn((new Column('salesAmount'))
                             ->setTitle($this->translate('salesAmount'))
                             ->setSortable(true)
                             ->setSearchable(true))
             ->addColumn((new Column('cost'))
                             ->setTitle($this->translate('cost'))
                             ->setSortable(true)
                             ->setSearchable(true))
             ->addColumn((new Column('grossProfit'))
                             ->setTitle($this->translate('grossProfit'))
                             ->setSortable(true)
                             ->setSearchable(true))
             ->addColumn((new Column('payment'))
                             ->setTitle($this->translate('payment'))
                             ->setSortable(true)
                             ->setSearchable(true))
             ->addColumn((new Column('paymentStatus'))
                             ->setTitle($this->translate('paymentStatus'))
                             ->setSortable(true)
                             ->setSearchable(true));

        $this->setRecordsPerPageOptions([10, 25]);
        $this->addToToolbar(new DownloadForm());
    }

    public function loadData(): void
    {
        $filters         = Request::get('ajaxData');//custom filter values
        $recordsProvider = new RecordsProvider();
        $rows            = $recordsProvider->getRecords($filters, true);

        $dataProv = new ArrayDataProvider($rows);
        $dataProv->setDefaultSorting('date', 'DESC');
        $this->setDataProvider($dataProv);
    }


    protected function parseDataSetRecords(): void
    {
        $this->dataSet->setFieldModifier('paymentStatus', function($fieldName, $row, $fieldValue) {

            if($fieldValue == 'Paid')
            {
                $label = new LabelSuccess();
            }
            elseif($fieldValue == 'Pending')
            {
                $label = new LabelWarning();
            }
            else
            {
                $label = new LabelDanger();
            }

            $label->setText($fieldValue);

            return $label;
        });

        $this->dataSet->setFieldModifier('salesAmount', function($fieldName, $row, $fieldValue) {
            return Helpers::formatDefaultCurrency($fieldValue);
        });

        $this->dataSet->setFieldModifier('grossProfit', function($fieldName, $row, $fieldValue) {
            return ($row['cost']) ? Helpers::formatDefaultCurrency($fieldValue) : "-";
        });

        $this->dataSet->setFieldModifier('cost', function($fieldName, $row, $fieldValue) {
            return ($fieldValue) ? Helpers::formatDefaultCurrency($fieldValue) : "-";
        });


        $this->dataSet->modifyRecords();
    }
}
