<?php

namespace TSSGGSModule\App\Libs\Support\Concerns;

use TSSGGSModule\App\Libs\Billing\FinancePolicy;
use TSSGGSModule\App\Libs\Products\CatalogueSync;
use TSSGGSModule\App\Libs\Products\ProductCatalog;
use TSSGGSModule\App\Libs\Products\ProductSettings;
use TSSGGSModule\App\Libs\Subscriptions\SubscriptionBilling;
use TSSGGSModule\App\Libs\Support\Helpers;
use TSSGGSModule\App\Models\RemoteProduct;
use TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use TSSGGSModule\App\Repositories\Whmcs\ProductRepository;
use TSSGGSModule\App\Models\Whmcs\Currency;
use TSSGGSModule\App\Models\Whmcs\Pricing;
use TSSGGSModule\App\Libs\Products\ConfigOptions\Quantity;
use TSSGGSModule\App\Libs\Products\ConfigOptions\SubOption;
use TSSGGSModule\App\Libs\Products\ConfigOptions\ConfigurableOptions;
use TSSGGSModule\App\Libs\Products\ConfigOptions\ConfigurableOptionsGroup;

/**
 * Re-reads the catalogue of a vendor and brings the configurable options of the products up to date.
 * Part of Helpers (used there as a trait).
 */
trait ProductResync
{
    public static function reSyncProducts($force = false)
    {
        RemoteProduct::synchronize();

        // Catalogue sync mode "Manually" (M-16): the nightly sync only reports the changes; the Re-sync button
        // ($force) still updates the products
        if(!$force && !CatalogueSync::fixes())
        {
            return;
        }

        $remoteProducts = RemoteProduct::get();

        foreach($remoteProducts as $remoteProduct)
        {
            $whmcsProduct = $remoteProduct->getWhmcsProduct();

            if($whmcsProduct)
            {
                $remoteProductData = $remoteProduct->rawData;

                $includedSan         = (int)($remoteProductData['san']['included']['single'] ?? 0);
                $includedSanWildcard = (int)($remoteProductData['san']['included']['wildcard'] ?? 0);

                $productRepository = new ProductRepository();
                $productRepository->updateProductConfiguration($whmcsProduct->id, [
                    'category'              => $remoteProduct->category,
                    'included_san'          => $includedSan,
                    'included_san_wildcard' => $includedSanWildcard,
                ]);


                $optionNames = [];

                if(!empty($remoteProductData['san']['single_allowed']))
                {
                    $optionNames['single'] = [
                        'name'         => 'sans',
                        'nameFriendly' => ProductCatalog::optionLabel('single', $includedSan),
                        'unit'         => 'SAN'
                    ];
                }

                if(!empty($remoteProductData['san']['wildcard_allowed']))
                {
                    $optionNames['wildcard'] = [
                        'name'         => 'sans_wildcard',
                        'nameFriendly' => ProductCatalog::optionLabel('wildcard', $includedSanWildcard),
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
                    $min = 0;

                    if($max <= 0)
                    {
                        $max = 0;
                    }

                    $configurableOptionsService = new ConfigurableOptions($whmcsProduct);
                    $configurableOptionModel    = $configurableOptionsService->getConfigurableOptionByName($localOptionName);

                    if($configurableOptionModel)
                    {
                        $configurableOptionModel->qtyminimum = $min;
                        $configurableOptionModel->qtymaximum = $max;
                        $configurableOptionModel->save();
                    }
                    else
                    {
                        $addonConfig      = (new AddonModuleRepository())->getModuleConfiguration();
                        $baseCurrencyRate = $addonConfig['financeSettings']['rate'] ?: 1;
                        $profitMargin     = floatval($addonConfig['financeSettings']['profitMargin']);
                        $baseCurrencyId   = Helpers::getCurrencyIdByCode('USD');

                        if(!$baseCurrencyId)
                        {
                            $baseCurrencyId = Helpers::getDefaultCurrencyId();
                        }

                        if(!$baseCurrencyId)
                        {
                            throw new \Exception("invalidCurrency");
                        }

                        $configurableOptionsGroupService = new ConfigurableOptionsGroup();
                        $group                           = $configurableOptionsGroupService->getFirstOrCreateRelated($whmcsProduct);

                        $configurableOption = (new Quantity($localOptionName, $localOptionNameFriendly))->setRange($min, $max);
                        $subOption          = (new SubOption($localOptionUnitName));

                        $configurableOption->addOption($subOption);
                        $configurableOption->setGroupId($group->id);
                        $configurableOption->create($whmcsProduct);

                        $configurableOptionsService = new ConfigurableOptions($whmcsProduct);
                        $configurableOptionModel    = $configurableOptionsService->getConfigurableOptionByName($localOptionName);

                        if(!$configurableOptionModel)
                        {
                            throw new \Exception("Unable to update configurable option");
                        }

                        $subOptionModel = $configurableOptionModel->suboptions()->first();
                        $pricingArray   = [];

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

                            $baseCost          = $price * $baseCurrencyRate;
                            $margin            = ProductSettings::margin($whmcsProduct->id, $profitMargin);
                            $currencies        = Currency::get();

                            foreach($currencies as $currency)
                            {
                                $pricingArray[$currency->id][$billingPeriod] = FinancePolicy::sell($baseCost, $margin, (int)$remotePriceData['term'], $currency->id, false);

                                if(SubscriptionBilling::isSubscriptionProduct($whmcsProduct->id) && $billingPeriod == 'annually')
                                {
                                    $pricingArray[$currency->id]['monthly'] = FinancePolicy::sell($baseCost, $margin, (int)$remotePriceData['term'], $currency->id, false);
                                }
                            }
                        }

                        foreach($pricingArray as $currencyId => $pricingData)
                        {
                            Pricing::where('type', 'configoptions')->where('currency', $currencyId)->where('relid', $subOptionModel->id)->update($pricingData);
                        }
                    }
                }
            }
        }
    }
}
