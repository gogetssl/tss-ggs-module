<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\ArrayDataProvider;


use ModulesGarden\TSSGGSModule\App\Libs\Helpers;

use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\Modals\ImportModal;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\Modals\ConfigurationModal;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\Modals\ReSyncModal;
use ModulesGarden\TSSGGSModule\Components\Button\Button;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonPrimary;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TSSGGSModule\Components\DataTable\Column;
use ModulesGarden\TSSGGSModule\Components\IconButton\IconButton;
use ModulesGarden\TSSGGSModule\Components\IconButton\IconButtonEdit;
use ModulesGarden\TSSGGSModule\Components\Label\LabelSuccess;
use ModulesGarden\TSSGGSModule\Components\VisibilityWrapper\VisibilityWrapper;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ModalLoad;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\DataProviders\ArrayDataProvider;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Product;
use WHMCS\User\Client;

class DataTable extends \ModulesGarden\TSSGGSModule\Components\DataTable\DataTable implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $this->setId('productsDataTable');

        $this->addColumn((new Column('id'))
                             ->setTitle($this->translate('id'))
                             ->setSortable()
                             ->setSearchable(true, Column::TYPE_INT))
             ->addColumn((new Column('productName'))
                             ->setTitle($this->translate('productName'))
                             ->setSortable(true)
                             ->setSearchable(true))
             ->addColumn((new Column('brand'))
                             ->setTitle($this->translate('brand'))
                             ->setSortable(true)
                             ->setSearchable(true))
             ->addColumn((new Column('validation'))
                             ->setTitle($this->translate('validation'))
                             ->setSortable(true)
                             ->setSearchable(true))
             ->addColumn((new Column('provider'))
                             ->setTitle($this->translate('provider'))
                             ->setSortable(true)
                             ->setSearchable(true))
             ->addColumn((new Column('status'))
                             ->setTitle($this->translate('status'))
                             ->setSortable(true)
                             ->setSearchable(true));

        $this->setRecordsPerPageOptions([10, 25]);

        $visibilityWrapper = new VisibilityWrapper((new IconButtonEdit())->onClick(new ModalLoad(new ConfigurationModal())));
        $visibilityWrapper->hideWhen('hidePricing', true);
        $this->addActionButton($visibilityWrapper);

        /*
                $visibilityWrapper = new VisibilityWrapper((new IconButton())->setTitle($this->translate('import'))->setIcon('import')->onClick(new ModalLoad(new ImportModal())));
                $visibilityWrapper->hideWhen('hideImport', true);
                $this->addActionButton($visibilityWrapper);
        */

        $this->addMassActionButton(
            (new ButtonSuccess())->setTitle($this->translate('import'))->setIcon('import')->onClick(new ModalLoad(new ImportModal()))
        );

        if($_REQUEST['mg-page'] != 'configuration')
        {
            $reSyncButton = new ButtonPrimary();
            $reSyncButton->setTitle($this->translate('reSync'));
            $reSyncButton->onClick(new ModalLoad(new ReSyncModal()));
            $this->addToToolbar($reSyncButton);
        }

    }

    public function loadData(): void
    {
        $rows = [];

        if(!RemoteProduct::isSynchronized())
        {
            RemoteProduct::synchronize();
        }

        $brandWeights = [
            'Digicert' => 1,
            'RapidSSL' => 2,
            'GeoTrust' => 3,
            'Sectigo'  => 99,
        ];

        $remoteProducts = RemoteProduct::get()->sortBy(function($product) use ($brandWeights) {
            return ($brandWeights[$product->brand] ?? 50) . $product->name;
        });

        foreach($remoteProducts as $remoteProduct)
        {
            $whmcsProduct = $remoteProduct->getWhmcsProduct();

            $rows[] = [
                'id'             => $remoteProduct->id,
                'whmcsProductId' => $whmcsProduct->id,
                'productName'    => $remoteProduct->name,
                'brand'          => $remoteProduct->brand,
                'validation'     => $remoteProduct->validation,
                'provider'       => Helpers::vendorToDisplay($remoteProduct->vendor),
                'status'         => $whmcsProduct ? $this->translate('imported') : '',
                'hidePricing'    => !$whmcsProduct,
                'hideImport'     => (bool)$whmcsProduct,
            ];
        }

        $dataProv = new ArrayDataProvider($rows);
        //$dataProv->setDefaultSorting('id', 'ASC');
        $this->setDataProvider($dataProv);
    }


    protected function parseDataSetRecords(): void
    {
        $this->dataSet->setFieldModifier('status', function($fieldName, $row, $fieldValue) {
            if($fieldValue)
            {
                $label = new LabelSuccess();
                $label->setText($fieldValue);
            }
            return $label;
        });

        $this->dataSet->modifyRecords();
    }
}
