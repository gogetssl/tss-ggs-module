<?php

namespace ModulesGarden\TSSGGSModule\App\Cron;

use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\Libs\GeneralSettings;
use ModulesGarden\TSSGGSModule\App\Libs\FinancePolicy;
use ModulesGarden\TSSGGSModule\App\Libs\RenewalPrices;
use ModulesGarden\TSSGGSModule\App\Libs\PricingService;
use ModulesGarden\TSSGGSModule\App\Libs\ProductSettings;
use ModulesGarden\TSSGGSModule\App\Models\CronCheck;
use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\ProductRepository;
use ModulesGarden\TSSGGSModule\Core\CommandLine\AbstractCommand;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Currency;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Pricing;
use ModulesGarden\TSSGGSModule\Packages\Logs\Support\Facades\Logger;
use ModulesGarden\TSSGGSModule\Packages\Product\Services\ConfigurableOptions;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class Sample
 * @package ModulesGarden\TSSGGSModule\App\Cron
 */
class ProductPricingUpdate extends AbstractCommand
{
    /**
     * Command name
     * @var string
     */
    protected $name = 'ProductPricingUpdate';

    /**
     * Command description
     * @var string
     */
    protected $description = 'Product Pricing Update';

    /**
     * Command help text
     * @var string
     */
    protected $help = '';

    /**
     * Configure command
     */
    protected function setup()
    {
    }

    /**
     * Run your custom code
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io)
    {
        $errorToDB = '';
        try
        {
            $checkCron = (new AddonModuleRepository())->checkCron('cron1');
            if($checkCron === false)
            {
                return;
            }

            RemoteProduct::synchronize();

            // catalogue refresh (formerly the separate job ReSyncProducts): announcements, account manager, product configuration
            $countryCode = \WHMCS\Config\Setting::getValue('DefaultCountry');
            foreach(Helpers::getConfiguredApis() as $configuredApi)
            {
                $configuredApi->getAnnouncements(true);
                $configuredApi->getManager($countryCode, true);
            }
            Helpers::reSyncProducts();

            // catalogue sync mode (M-16): retire / hide discontinued products, show returned ones, import new ones
            $catalogue = \ModulesGarden\TSSGGSModule\App\Libs\CatalogueSync::apply();
            if(array_sum($catalogue))
            {
                Logger::info('Catalogue sync: ' . json_encode($catalogue));
            }

            //$baseCurrencyId - USD or default WHMCS currency
            $baseCurrencyId = Helpers::getCurrencyIdByCode('USD');

            if(!$baseCurrencyId)
            {
                $baseCurrencyId = Helpers::getDefaultCurrencyId();
            }

            if(!$baseCurrencyId)
            {
                $io->write('Error: Invalid base currency.');
                return;
            }

            $addonConfig      = (new AddonModuleRepository())->getModuleConfiguration();
            $baseCurrencyRate = FinancePolicy::refreshRate(); // the daily rate when that source is chosen
            $profitMargin     = floatval($addonConfig['financeSettings']['profitMargin']);

            $remoteProducts = RemoteProduct::get();

            $io->title('Progress');
            $io->progressStart($remoteProducts->count());

            foreach($remoteProducts as $remoteProduct)
            {
                $io->progressAdvance(1);

                $remoteProductData = $remoteProduct->rawData;
                $whmcsProduct      = $remoteProduct->getWhmcsProduct();

                if(!$whmcsProduct)
                {
                    continue;
                }

                $productRepository    = new ProductRepository();
                $productConfiguration = $productRepository->getProductConfiguration($whmcsProduct->id);

                if(strtolower($productConfiguration['price_auto']) != 'on')
                {
                    continue;
                }

                $margin = ProductSettings::margin($whmcsProduct->id, $profitMargin);

                foreach($remoteProductData['prices'] as $remotePriceData)
                {
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
                        $pricing      = Pricing::where('type', 'product')->where('currency', $currency->id)->where('relid', $whmcsProduct->id)->first();
                        $billingCycle = Helpers::monthsToBillingPeriod($remotePriceData['term']);

                        if($billingCycle === false)
                        {
                            continue;
                        }

                        $currencyPrice = FinancePolicy::sell($baseCost, $margin, (int)$remotePriceData['term'], $currency->id, true);

                        Pricing::updateOrInsert(
                            ['type' => 'product', 'currency' => $currency->id, 'relid' => $whmcsProduct->id],
                            [$billingCycle => $currencyPrice]
                        );

                        if($whmcsProduct->paytype == 'onetime' && $billingCycle == 'annually')
                        {
                            Pricing::updateOrInsert(
                                ['type' => 'product', 'currency' => $currency->id, 'relid' => $whmcsProduct->id],
                                ['monthly' => $currencyPrice]
                            );
                        }
                    }

                    Logger::info("Pricing for product #{$whmcsProduct->id} has been updated");
                }

                // code signing: token shipping fees of the "Certificate Delivery" option
                if(strtolower((string)$remoteProduct->category) === \ModulesGarden\TSSGGSModule\App\Libs\CodeSigningService::CATEGORY)
                {
                    \ModulesGarden\TSSGGSModule\App\Libs\CodeSigningService::syncDeliveryPrices($whmcsProduct->id, (array)$remoteProductData);
                }

                $expectedOptionNames = [
                    'single'   => 'sans',
                    'wildcard' => 'sans_wildcard',
                ];

                $configurableOptionsService = new ConfigurableOptions($whmcsProduct);

                foreach($expectedOptionNames as $remoteOptionName => $localOptionName)
                {
                    $configurableOption = $configurableOptionsService->getConfigurableOptionByName($localOptionName);

                    if(!$configurableOption)
                    {
                        continue;
                    }

                    $subOption = $configurableOption->suboptions()->first();

                    if(!$subOption)
                    {
                        continue;
                    }

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
                            $pricingArray[$currency->id][$billingPeriod] = FinancePolicy::sell($baseCost, $margin, (int)$remotePriceData['term'], $currency->id, false);

                            if($whmcsProduct->paytype == 'onetime' && $billingPeriod == 'annually')
                            {
                                $pricingArray[$currency->id]['monthly'] = FinancePolicy::sell($baseCost, $margin, (int)$remotePriceData['term'], $currency->id, false);
                            }
                        }
                    }

                    foreach($pricingArray as $currencyId => $pricingData)
                    {
                        Pricing::where('type', 'configoptions')->where('currency', $currencyId)->where('relid', $subOption->id)->update($pricingData);
                    }

                    Logger::info("Configurable Options Pricing for product #{$whmcsProduct->id} has been updated");
                }

                // FLEX products: the wildcard main domain surcharge (created when missing, then priced like a SAN option)
                if(\ModulesGarden\TSSGGSModule\App\Libs\MainDomain::isChoice((array)$remoteProductData, (string)$whmcsProduct->configoption1))
                {
                    \ModulesGarden\TSSGGSModule\App\Libs\MainDomain::ensureOption((int)$whmcsProduct->id, (array)$remoteProductData);
                    $wildcardSub = \WHMCS\Database\Capsule::table('tblproductconfiglinks')
                        ->join('tblproductconfigoptions', 'tblproductconfigoptions.gid', '=', 'tblproductconfiglinks.gid')
                        ->join('tblproductconfigoptionssub', 'tblproductconfigoptionssub.configid', '=', 'tblproductconfigoptions.id')
                        ->where('tblproductconfiglinks.pid', (int)$whmcsProduct->id)
                        ->where('tblproductconfigoptions.optionname', 'like', \ModulesGarden\TSSGGSModule\App\Libs\MainDomain::OPTION . '|%')
                        ->where('tblproductconfigoptionssub.optionname', 'like', \ModulesGarden\TSSGGSModule\App\Libs\MainDomain::WILDCARD . '|%')
                        ->value('tblproductconfigoptionssub.id');
                    if($wildcardSub)
                    {
                        foreach($remoteProductData['prices'] as $remotePriceData)
                        {
                            $billingPeriod = Helpers::monthsToBillingPeriod($remotePriceData['term']);
                            $extra         = \ModulesGarden\TSSGGSModule\App\Libs\MainDomain::wildcardCost((array)$remoteProductData, (int)$remotePriceData['term']);
                            if($billingPeriod === false || $extra === null)
                            {
                                continue;
                            }
                            foreach(Currency::get() as $currency)
                            {
                                $value   = FinancePolicy::sell($extra * $baseCurrencyRate, $margin, (int)$remotePriceData['term'], $currency->id, false);
                                $columns = [$billingPeriod => $value];
                                if($whmcsProduct->paytype == 'onetime' && $billingPeriod == 'annually')
                                {
                                    $columns['monthly'] = $value;
                                }
                                Pricing::where('type', 'configoptions')->where('currency', $currency->id)->where('relid', (int)$wildcardSub)->update($columns);
                            }
                        }
                    }
                }
            }

            // Manual prices follow cost changes (same margin); admins hear about prices below cost
            $pricing = new PricingService();
            $kept    = $pricing->keepMargins(GeneralSettings::enabled('keepMarginOnSync'));
            if($kept)
            {
                Logger::info("Manual prices adjusted to keep the margin: {$kept}");
            }
            if(FinancePolicy::raiseBelowCost())
            {
                $raised = $pricing->raiseBelowCost($profitMargin, GeneralSettings::enabled('belowCostEmail'));
                if($raised)
                {
                    Logger::info('Prices raised to cover the reseller cost: ' . count($raised));
                }
            }
            if(GeneralSettings::enabled('belowCostEmail'))
            {
                $pricing->notifyBelowCost();
            }

            // product addons that sell a certificate product (M-14) keep its prices
            $addons = \ModulesGarden\TSSGGSModule\App\Libs\ProductAddons::syncAll();
            if($addons)
            {
                Logger::info('Product addon prices updated from their certificate products: ' . $addons);
            }

            // existing services renew at the current price (Settings > Finance > Renewal prices)
            $renewals = RenewalPrices::sync();
            if($renewals['updated'])
            {
                Logger::info('Renewal prices of existing services updated: ' . $renewals['updated'] . ' (raised ' . $renewals['raised'] . ', lowered ' . $renewals['lowered'] . ')');
            }

            // what changed in the provider's catalogue since the last e-mail, to the administrators (M-16)
            \ModulesGarden\TSSGGSModule\App\Libs\CatalogueSync::notify();
        }
        catch(\Throwable $exception)
        {
            Logger::error('ProductPricingUpdate Cron Error: ' . $exception->getMessage(), \ModulesGarden\TSSGGSModule\App\Libs\LogContext::exception($exception));
            $errorToDB = $exception->getMessage();
        }


        CronCheck::updateOrCreate(
            ['type' => 'ProductPricingUpdate'],
            ['last_run' => date('Y-m-d H:i:s'), 'last_error' => trim($errorToDB)]
        );

        $io->progressFinish();
        $io->success('Finished!');
    }
}
