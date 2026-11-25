<?php

namespace TSSGGSModule\App\Libs\Products;

use TSSGGSModule\App\Libs\Subscriptions\AcmeHelpers;
use TSSGGSModule\App\Libs\Billing\FinancePolicy;
use TSSGGSModule\App\Libs\Support\Helpers;
use TSSGGSModule\App\Models\RemoteProduct;
use TSSGGSModule\App\Repositories\Whmcs\ProductRepository;
use TSSGGSModule\App\Models\Whmcs\Currency;
use TSSGGSModule\App\Models\Whmcs\Pricing;
use TSSGGSModule\App\Models\Whmcs\Product;
use TSSGGSModule\App\Models\Whmcs\ProductGroup;
use TSSGGSModule\App\Libs\Products\ConfigOptions\Quantity;
use TSSGGSModule\App\Libs\Products\ConfigOptions\SubOption;
use TSSGGSModule\App\Libs\Products\ConfigOptions\ConfigurableOptions;

/**
 * Import of vendor products into WHMCS (admin page Products, the setup wizard, the nightly catalogue sync): for each
 * product a WHMCS product with prices in every currency (reseller cost x rate + margin, FinancePolicy), the module
 * settings of the product and its configurable options (SANs, code signing delivery, FLEX main domain).
 */
class ProductImport
{
    /**
     * @param int[]      $remoteIds IDs of the synchronized vendor products (RemoteProduct) to import
     * @param int|string $group     WHMCS product group ID, or "new" to create the group $groupName
     * @param float      $rate      exchange rate from USD to the default currency, used when WHMCS has no USD
     * @param float      $margin    margin in % added to the reseller cost
     * @throws \Exception "invalidCurrency", "productCreationFailed"
     */
    public static function run(array $remoteIds, $group, string $groupName = '', float $rate = 1, float $margin = 0): void
    {
        if($group === 'new')
        {
            $productGroupName = trim($groupName);
            $slug             = strtolower($productGroupName);
            $slug             = str_replace(' ', '-', $slug);
            $slugIndex        = '';
            $index            = 1;

            while(true)
            {
                $existingGroup = ProductGroup::where('slug', $slug . $slugIndex)->first();

                if($existingGroup)
                {
                    $slugIndex = $index;
                    $index++;
                }
                else
                {
                    $slug = $slug . $slugIndex;
                    break;
                }
            }

            $productGroupModel         = new ProductGroup();
            $productGroupModel->name   = $productGroupName;
            $productGroupModel->slug   = $slug;
            $productGroupModel->hidden = 0;
            $productGroupModel->save();

            $productGroupId = $productGroupModel->id;
        }
        else
        {
            $productGroupId = (int)$group;
        }

        $localIds           = $remoteIds;
        $baseCurrencyRate   = $rate ?: 1;
        $importMargin       = max(0, $margin);

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

            // an imported product is never created a second time (ticked again, or the form sent twice)
            if(!$remoteProduct || !\TSSGGSModule\App\Libs\Products\ProductCatalog::importable($remoteProduct) || $remoteProduct->getWhmcsProduct())
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

                $baseCost   = $apiPrice * $baseCurrencyRate;
                $currencies        = Currency::get();

                foreach($currencies as $currency)
                {
                    $pricing[$currency->id][$billingPeriod] = FinancePolicy::sell($baseCost, $importMargin, (int)$remotePriceData['term'], $currency->id, true);

                    if(\TSSGGSModule\App\Libs\Subscriptions\SubscriptionBilling::isSubscriptionRemote($remoteProduct) && $billingPeriod == 'annually')
                    {
                        $pricing[$currency->id]['monthly'] = FinancePolicy::sell($baseCost, $importMargin, (int)$remotePriceData['term'], $currency->id, true);
                    }
                }
            }

            $productRepository = new ProductRepository();
            $productId         = $productRepository->createProduct($productName, $productDescription, $productGroupId, $pricing);

            if($productId)
            {
                $dcvArray            = $remoteProductData['dcv'] ?? [];
                $includedSan         = (int)($remoteProductData['san']['included']['single'] ?? 0);
                $includedSanWildcard = (int)($remoteProductData['san']['included']['wildcard'] ?? 0);

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

            // code signing: the "Certificate Delivery" option (token shipping as a one-time fee)
            if(strtolower((string)$remoteProduct->category) === \TSSGGSModule\App\Libs\ProductLines\CodeSigningService::CATEGORY)
            {
                \TSSGGSModule\App\Libs\ProductLines\CodeSigningService::createDeliveryOption($productId, (array)$remoteProductData);
            }

            //create configOptions

            $product = Product::find($productId);

            // subscriptions: one-time products, the module bills the yearly renewal (SubscriptionRenewals)
            if(\TSSGGSModule\App\Libs\Subscriptions\SubscriptionBilling::isSubscriptionProduct($productId))
            {
                $product->paytype = 'onetime';
                $product->configoptionsupgrade = AcmeHelpers::isAcmeWhmcsProduct($productId) ? 1 : $product->configoptionsupgrade;
                $product->save();
            }

            if(!$product)
            {
                continue;
            }

            $optionNames = [];

            // the client sees these names on the order form: say what is already in the price
            if(!empty($remoteProductData['san']['single_allowed']))
            {
                $optionNames['single'] = [
                    'name'         => 'sans',
                    'nameFriendly' => \TSSGGSModule\App\Libs\Products\ProductCatalog::optionLabel('single', $includedSan),
                    'unit'         => 'SAN'
                ];
            }

            if(!empty($remoteProductData['san']['wildcard_allowed']))
            {
                $optionNames['wildcard'] = [
                    'name'         => 'sans_wildcard',
                    'nameFriendly' => \TSSGGSModule\App\Libs\Products\ProductCatalog::optionLabel('wildcard', $includedSanWildcard),
                    'unit'         => 'SAN'
                ];
            }

            // SAN options: let clients buy more SANs later (WHMCS "Configurable Options Upgrades")
            if($optionNames && !(int)$product->configoptionsupgrade)
            {
                $product->configoptionsupgrade = 1;
                $product->save();
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

                        $baseCost          = $apiPrice * $baseCurrencyRate;
                        $currencies        = Currency::get();

                        foreach($currencies as $currency)
                        {
                            $pricingArray[$currency->id][$billingPeriod] = FinancePolicy::sell($baseCost, $importMargin, (int)$remotePriceData['term'], $currency->id, false);

                            if(\TSSGGSModule\App\Libs\Subscriptions\SubscriptionBilling::isSubscriptionRemote($remoteProduct) && $billingPeriod == 'annually')
                            {
                                $pricingArray[$currency->id]['monthly'] = FinancePolicy::sell($baseCost, $importMargin, (int)$remotePriceData['term'], $currency->id, false);
                            }
                        }
                    }

                    foreach($pricingArray as $currencyId => $pricingData)
                    {
                        Pricing::where('type', 'configoptions')->where('currency', $currencyId)->where('relid', $subOption->id)->update($pricingData);
                    }
                }
            }

            // FLEX products: the client chooses a standard or a wildcard main domain (the wildcard costs more)
            \TSSGGSModule\App\Libs\Certificates\MainDomain::ensureOption((int)$productId, (array)$remoteProductData, $importMargin, (float)$baseCurrencyRate);
        }

        // CodeGuard plans can be upgraded to the dearer ones (the only product type that switches plans at the vendor)
        \TSSGGSModule\App\Libs\ProductLines\CodeGuardService::linkUpgradePaths();
    }
}
