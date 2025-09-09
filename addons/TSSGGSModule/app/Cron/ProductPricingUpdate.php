<?php

namespace ModulesGarden\TSSGGSModule\App\Cron;

use Exception;
use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\Libs\SSLTrustCenterApi;
use ModulesGarden\TSSGGSModule\App\Models\CronCheck;
use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TSSGGSModule\App\Models\Request;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\ProductRepository;
use ModulesGarden\TSSGGSModule\Core\CommandLine\AbstractCommand;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Currency;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Pricing;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Service;
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
        try
        {
            $checkCron = (new AddonModuleRepository())->checkCron('cron1');
            if($checkCron === false)
            {
                return;
            }

            RemoteProduct::synchronize();

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
            $baseCurrencyRate = $addonConfig['financeSettings']['rate'] ?: 1;
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
                    else
                    {
                        continue;
                    }

                    $baseCurrencyPrice = $apiPrice * $baseCurrencyRate;
                    $baseCurrencyPrice = $baseCurrencyPrice + ($baseCurrencyPrice * $profitMargin / 100);
                    $currencies        = Currency::get();

                    foreach($currencies as $currency)
                    {
                        $pricing      = Pricing::where('type', 'product')->where('currency', $currency->id)->where('relid', $whmcsProduct->id)->first();
                        $billingCycle = Helpers::monthsToBillingPeriod($remotePriceData['term']);

                        if($billingCycle === false || $pricing->{$billingCycle} < 0)
                        {
                            continue;
                        }

                        $currencyPrice = \convertCurrency($baseCurrencyPrice, $baseCurrencyId, $currency->id);

                        Pricing::updateOrInsert(
                            ['type' => 'product', 'currency' => $currency->id, 'relid' => $whmcsProduct->id],
                            [$billingCycle => $currencyPrice]
                        );
                    }

                    Logger::info("Pricing for product #{$whmcsProduct->id} has been updated");
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

                    Logger::info("Configurable Options Pricing for product #{$whmcsProduct->id} has been updated");
                }
            }
        }
        catch(\Throwable $exception)
        {
            Logger::error('ProductPricingUpdate Cron Error: ' . $exception->getMessage());
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