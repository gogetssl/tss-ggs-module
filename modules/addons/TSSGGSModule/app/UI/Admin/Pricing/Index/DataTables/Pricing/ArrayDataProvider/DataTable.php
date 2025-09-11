<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Pricing\Index\DataTables\Pricing\ArrayDataProvider;


use ModulesGarden\TSSGGSModule\App\Libs\Helpers;

use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\ProductRepository;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Pricing\Index\DataTables\Pricing\Modals\PricingModal;
use ModulesGarden\TSSGGSModule\Components\DataTable\Column;
use ModulesGarden\TSSGGSModule\Components\IconButton\IconButtonEdit;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ModalLoad;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\DataProviders\ArrayDataProvider;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Currency;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Pricing;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Product;
use WHMCS\User\Client;

class DataTable extends \ModulesGarden\TSSGGSModule\Components\DataTable\DataTable implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $this->setId('PricingDataTable');

        $this->addColumn((new Column('id'))
                             ->setTitle($this->translate('id'))
                             ->setSortable()
                             ->setSearchable(true, Column::TYPE_INT))
             ->addColumn((new Column('productName'))
                             ->setTitle($this->translate('productName'))
                             ->setSortable(true)
                             ->setSearchable(true))
             ->addColumn((new Column('annuallyPrice'))
                             ->setTitle($this->translate('annuallyPrice'))
                             ->setSortable(true)
                             ->setSearchable(true))
             ->addColumn((new Column('bienniallyPrice'))
                             ->setTitle($this->translate('bienniallyPrice'))
                             ->setSortable(true)
                             ->setSearchable(true))
             ->addColumn((new Column('trienniallyPrice'))
                             ->setTitle($this->translate('trienniallyPrice'))
                             ->setSortable(true)
                             ->setSearchable(true));

        /*
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
                         ->setSearchable(true));
        */

        $this->setRecordsPerPageOptions([10, 25]);
        $this->addActionButton((new IconButtonEdit())->onClick(new ModalLoad(new PricingModal())));
    }

    public function loadData(): void
    {
        $whmcsProducts = Product::where('servertype', 'TSSGGSModule')->get();
        $rows          = [];

        foreach($whmcsProducts as $whmcsProduct)
        {
            $vendor          = $whmcsProduct->configoption1;
            $remoteProductId = $whmcsProduct->configoption2;
            $remoteProduct   = RemoteProduct::where('remoteId', $remoteProductId)->where('vendor', $vendor)->first();

            $row = [
                'id'             => $whmcsProduct->id,
                'whmcsProductId' => $whmcsProduct->id,
                'productName'    => $whmcsProduct->name,
                'brand'          => $remoteProduct->brand,
                'validation'     => $remoteProduct->validation,
                'provider'       => Helpers::vendorToDisplay($vendor),
            ];


            $productRepository                = new ProductRepository();
            $productConfiguration             = $productRepository->getProductConfiguration($whmcsProduct->id);
            $this->data['auto_update_enable'] = ($productConfiguration['price_auto'] == 'on');

            $currencyCode = Request::get('ajaxData')['currency'];

            if($currencyCode)
            {
                $currency = Currency::where('code', $currencyCode)->first();
            }
            else
            {
                $currency = Currency::first();
            }

            $row['currencyCode'] = $currency->code;

            $pricing = Pricing::where('type', 'product')
                              ->where('relid', $whmcsProduct->id)
                              ->where('currency', $currency->id)
                              ->first();

            $configurableOptionsData = Helpers::getConfigurableOptionsData($whmcsProduct->id, $currency->id);

            foreach($configurableOptionsData as $configurableOptionData)
            {
                $parts      = explode('|', $configurableOptionData->optionname);
                $optionName = $parts[1] ?: $parts[0];

                $row['productName'] .= '<br>+ ' . $optionName;
            }

            $expectedBillingCycles = [
                'annually',
                'biennially',
                'triennially'
            ];

            foreach($expectedBillingCycles as $billingCycle)
            {
                $price  = $pricing->{$billingCycle};

                if(!$pricing || $price < 0)
                {
                    $price  = '<span  class="lu-label lu-tooltip lu-label--danger lu-label--sm">'.$this->translate('disabled').'</span>';
                }
                else
                {
                    $price = formatCurrency($price, $currency->id);

                    foreach($configurableOptionsData as $configurableOptionData)
                    {
                        $optionPrice = (float)$configurableOptionData->{$billingCycle};
                        $optionPrice = formatCurrency($optionPrice, $currency->id);
                        $price .= '<br>' . $optionPrice;

                    }
                }

                $row[$billingCycle . 'Price'] = (string)$price;
            }

            $rows[] = $row;
        }

        $dataProv = new ArrayDataProvider($rows);
        $dataProv->setDefaultSorting('id', 'ASC');
        $this->setDataProvider($dataProv);
    }

    protected function parseDataSetRecords(): void
    {
        $this->dataSet->setFieldModifier('id', function($fieldName, $row, $fieldValue) {
            return $fieldValue;
        });

        $this->dataSet->modifyRecords();
    }
}
