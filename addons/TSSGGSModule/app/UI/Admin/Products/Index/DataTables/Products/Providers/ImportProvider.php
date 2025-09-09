<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\Providers;

use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\ProductRepository;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Currency;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Pricing;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Product;
use ModulesGarden\TSSGGSModule\Packages\Product\Libs\ConfigurableOptions\Quantity;
use ModulesGarden\TSSGGSModule\Packages\Product\Libs\ConfigurableOptions\SubOption\SubOption;
use ModulesGarden\TSSGGSModule\Packages\Product\Services\ConfigurableOptions;

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
        $baseCurrencyRate   = $this->formData['rate'] ?: 1;
        $pricingTypePercent = (bool)$this->formData['pricingTypePercent'];

        //$baseCurrencyId - USD or default WHMCS currency
        $baseCurrencyId = Helpers::getCurrencyIdByCode('USD');

        if(!$baseCurrencyId)
        {
            $baseCurrencyId = Helpers::getDefaultCurrencyId();
        }

        if(!$baseCurrencyId)
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
                    $apiPrice = floatval($remotePriceData['base']['single']['selling']);
                }
                elseif(isset($remotePriceData['base']['wildcard']['selling']))
                {
                    $apiPrice = floatval($remotePriceData['base']['wildcard']['selling']);
                }
                elseif(isset($remotePriceData['selling']))
                {
                    $apiPrice = floatval($remotePriceData['selling']);
                }
                else
                {
                    continue;
                }

                if(isset($this->formData['profitMargin']) && floatval($this->formData['profitMargin']) > 0)
                {
                    $profitMargin = floatval($this->formData['profitMargin']);
                    $apiPrice     = $apiPrice + ($apiPrice * $profitMargin / 100);
                }

                $baseCurrencyPrice = $apiPrice * $baseCurrencyRate;
                $currencies        = Currency::get();

                foreach($currencies as $currency)
                {
                    $pricing[$currency->id][$billingPeriod] = \convertCurrency($baseCurrencyPrice, $baseCurrencyId, $currency->id);
                }
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
                    //'price_auto'            => $pricingTypePercent ? 'on' : 'off',
                    'price_auto'            => 'on', //always on
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
                    $pricingArray = [];

                    foreach($remoteProductData['prices'] as $remotePriceData)
                    {
                        $billingPeriod = Helpers::monthsToBillingPeriod($remotePriceData['term']);

                        if($billingPeriod === false)
                        {
                            continue;
                        }

                        if(isset($remotePriceData['san'][$remoteOptionName]['selling']))
                        {
                            $apiPrice = floatval($remotePriceData['san'][$remoteOptionName]['selling']);
                        }
                        else
                        {
                            continue;
                        }

                        $apiPrice          = $apiPrice + ($apiPrice * $profitMargin / 100);
                        $baseCurrencyPrice = $apiPrice * $baseCurrencyRate;
                        $currencies        = Currency::get();

                        foreach($currencies as $currency)
                        {
                            $pricingArray[$currency->id][$billingPeriod] = \convertCurrency($baseCurrencyPrice, $baseCurrencyId, $currency->id);
                        }
                    }

                    foreach($pricingArray as $currencyId => $pricingData)
                    {
                        Pricing::where('type', 'configoptions')->where('currency', $currencyId)->where('relid', $subOption->id)->update($pricingData);
                    }
                }
            }
        }
    }
}