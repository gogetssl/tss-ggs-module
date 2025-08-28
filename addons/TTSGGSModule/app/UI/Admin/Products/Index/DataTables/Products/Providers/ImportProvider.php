<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\Providers;

use ModulesGarden\TTSGGSModule\App\Libs\Helpers;
use ModulesGarden\TTSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\ProductRepository;
use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Pricing;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Product;
use ModulesGarden\TTSGGSModule\Packages\Product\Libs\ConfigurableOptions\Quantity;
use ModulesGarden\TTSGGSModule\Packages\Product\Libs\ConfigurableOptions\SubOption\SubOption;
use ModulesGarden\TTSGGSModule\Packages\Product\Services\ConfigurableOptions;

class ImportProvider extends CrudProvider
{
    public function read()
    {
        $this->data = $this->formData;

        $this->availableValues['productGroup'] = Helpers::getProductGroupOptions();
        $this->availableValues['currency']     = Helpers::getCurrencyOptions();

        $ajaxData = Request::get('ajaxData');
        $formData = $this->formData;
        $mode     = 'individual';

        if(isset($ajaxData['reloadedBy']) && $ajaxData['reloadedBy'] == 'pricingTypePercent')
        {
            $mode = ((bool)$formData['pricingTypePercent']) ? "percent" : "individual";
        }
        elseif(isset($ajaxData['reloadedBy']) && $ajaxData['reloadedBy'] == 'pricingTypeIndividual')
        {
            $mode = ((bool)$formData['pricingTypeIndividual']) ? "individual" : "percent";
        }

        $this->data['pricingTypeIndividual'] = ($mode == 'individual');
        $this->data['pricingTypePercent']    = ($mode == 'percent');
    }

    public function create()
    {
        $productGroupId     = (int)$this->formData['productGroup'];
        $localIds           = explode(',', $this->formData['id']);
        $currencyId         = (int)$this->formData['currency'];
        $currencyRate       = $this->formData['rate'] ?: 1;
        $pricingTypePercent = (bool)$this->formData['pricingTypePercent'];


        if(!$currencyId)
        {
            throw new \Exception("invalidCurrency");
        }

        foreach($localIds as $localId)
        {
            $remoteProduct = RemoteProduct::find($localId);

            if(!$remoteProduct)
            {
                continue;
            }

            $productName        = $remoteProduct->name;
            $productDescription = $remoteProduct->description;
            $remoteProductData  = $remoteProduct->rawData;
            $pricing            = [];

            foreach($remoteProductData['prices'] as $remotePriceData)
            {
                $billingPeriod = Helpers::monthsToBillingPeriod($remotePriceData['term']);

                if($billingPeriod === false)
                {
                    continue;
                }

                if(isset($remotePriceData['base']['single']['selling']))
                {
                    $price = floatval($remotePriceData['base']['single']['selling']);
                }
                elseif(isset($remotePriceData['base']['wildcard']['selling']))
                {
                    $price = floatval($remotePriceData['base']['wildcard']['selling']);
                }
                else
                {
                    continue;
                }

                if(isset($this->formData['profitMargin']) && floatval($this->formData['profitMargin']) > 0)
                {
                    $profitMargin = floatval($this->formData['profitMargin']);
                    $price        = $price + ($price * $profitMargin / 100);
                }

                $currencyPrice                        = $price * $currencyRate;
                $pricing[$currencyId][$billingPeriod] = $currencyPrice;
            }


            $productRepository = new ProductRepository();
            $productId         = $productRepository->createProduct($productName, $productDescription, $productGroupId, $pricing);

            if($productId)
            {
                $dcvArray            = $remoteProductData['dcv'] ?: [];
                $includedSan         = (int)$remoteProductData['san']['included']['single'];
                $includedSanWildcard = (int)$remoteProductData['san']['included']['wildcard'];

                $productRepository->updateProductConfiguration($productId, [
                    'provider'              => $remoteProduct->vendor,
                    'product_id'            => $remoteProduct->remoteId,
                    'brand'                 => $remoteProduct->brand,
                    'validation'            => $remoteProduct->validation,
                    'category'              => $remoteProduct->category,
                    'dcv'                   => implode(',', $dcvArray),
                    'price_auto'            => $pricingTypePercent ? 'on' : 'off',
                    'included_san'          => $includedSan,
                    'included_san_wildcard' => $includedSanWildcard,
                ]);
            }
            else
            {
                throw new \Exception('productCreationFailed');
            }

            //create configOptions

            $product = Product::find($productId);

            if(!$product)
            {
                continue;
            }

            $optionNames = [];

            if($remoteProductData['san']['single_allowed'])
            {
                $optionNames['single'] = [
                    'name'         => 'sans',
                    'nameFriendly' => 'Additional Single domain SAN\'s',
                    'unit'         => 'SAN'
                ];
            }

            if($remoteProductData['san']['wildcard_allowed'])
            {
                $optionNames['wildcard'] = [
                    'name'         => 'sans_wildcard',
                    'nameFriendly' => 'Additional Wildcard domain SAN\'s',
                    'unit'         => 'SAN'
                ];
            }

            foreach($optionNames as $remoteOptionName => $localOptionNames)
            {
                $localOptionName         = $localOptionNames['name'];
                $localOptionNameFriendly = $localOptionNames['nameFriendly'];
                $localOptionUnitName     = $localOptionNames['unit'];
                $min                     = $remoteProductData['san']['min'] ?: 0;
                $max                     = $remoteProductData['san']['max'] ?: 0;
                $included                = 0;

                if($remoteOptionName == 'single')
                {
                    $included = $includedSan;
                }
                elseif($remoteOptionName == 'wildcard')
                {
                    $included = $includedSanWildcard;
                }

                $max = $max - ($included - $min);
                $min = $min - $included;

                if($min < 0)
                {
                    $min = 0;
                }

                if($max < 0)
                {
                    $max = 0;
                }

                $configurableOption = (new Quantity($localOptionName, $localOptionNameFriendly))->setRange($min, $max);
                $subOption          = (new SubOption($localOptionUnitName));

                $configurableOption->addOption($subOption);
                $configurableOptionsService = new ConfigurableOptions($product);
                $configurableOptionModel    = $configurableOptionsService->createConfigurableOption($configurableOption);
                $subOptions                 = $configurableOptionModel->suboptions()->get();

                foreach($subOptions as $subOption)
                {
                    $pricing                = new Pricing();
                    $insertData             = [];
                    $insertData['type']     = 'configoptions';
                    $insertData['currency'] = $currencyId;
                    $insertData['relid']    = $subOption->id;

                    foreach($pricing->priceFields() as $cycle)
                    {
                        $insertData[$cycle] = 0;
                    }

                    foreach($pricing->setupFields() as $setup)
                    {
                        $insertData[$setup] = 0;
                    }

                    foreach($remoteProductData['prices'] as $remotePriceData)
                    {
                        $billingPeriod = Helpers::monthsToBillingPeriod($remotePriceData['term']);

                        if($billingPeriod === false)
                        {
                            continue;
                        }

                        if(isset($remotePriceData['san'][$remoteOptionName]['selling']))
                        {
                            $price = floatval($remotePriceData['san'][$remoteOptionName]['selling']);
                        }
                        else
                        {
                            continue;
                        }

                        if(isset($this->formData['profitMargin']) && floatval($this->formData['profitMargin']) > 0)
                        {
                            $profitMargin = floatval($this->formData['profitMargin']);
                            $price        = $price + ($price * $profitMargin / 100);
                        }

                        $currencyPrice              = $price * $currencyRate;
                        $insertData[$billingPeriod] = $currencyPrice;
                    }

                    Pricing::where('type', 'configoptions')->where('currency', $currencyId)->where('relid', $subOption->id)->update($insertData);
                }
            }
        }
    }
}