<?php

namespace ModulesGarden\TTSGGSModule\App\Cron;

use Exception;
use ModulesGarden\TTSGGSModule\App\Libs\Helpers;
use ModulesGarden\TTSGGSModule\App\Libs\SSLTrustCenterApi;
use ModulesGarden\TTSGGSModule\App\Models\CronCheck;
use ModulesGarden\TTSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TTSGGSModule\App\Models\Request;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\ProductRepository;
use ModulesGarden\TTSGGSModule\Core\CommandLine\AbstractCommand;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Currency;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Pricing;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Service;
use ModulesGarden\TTSGGSModule\Packages\Logs\Support\Facades\Logger;
use ModulesGarden\TTSGGSModule\Packages\Product\Services\ConfigurableOptions;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class Sample
 * @package ModulesGarden\TTSGGSModule\App\Cron
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

            $addonConfig  = (new AddonModuleRepository())->getModuleConfiguration();
            $currencyId   = (int)$addonConfig['financeSettings']['currency'];
            $currency     = Currency::find($currencyId);
            $currencyRate = $addonConfig['financeSettings']['rate'] ?: 1;
            $profitMargin = floatval($addonConfig['financeSettings']['profitMargin']);

            if(!$currency)
            {
                $io->write('Error: Invalid currency id.');
            }

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

                $pricing = Pricing::where('type', 'product')->where('currency', $currency->id)->where('relid', $whmcsProduct->id)->first();

                foreach($remoteProductData['prices'] as $remotePriceData)
                {
                    $billingCycle = Helpers::monthsToBillingPeriod($remotePriceData['term']);

                    if($billingCycle === false || $pricing->{$billingCycle} < 0)
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

                    $price         = $price + ($price * $profitMargin / 100);
                    $currencyPrice = $price * $currencyRate;

                    Pricing::updateOrInsert(
                        ['type' => 'product', 'currency' => $currency->id, 'relid' => $whmcsProduct->id],
                        [$billingCycle => $currencyPrice]
                    );

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

                    $insertData = [];

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

                        $price                      = $price + ($price * $profitMargin / 100);
                        $currencyPrice              = $price * $currencyRate;
                        $insertData[$billingPeriod] = $currencyPrice;
                    }

                    Pricing::where('type', 'configoptions')->where('currency', $currencyId)->where('relid', $subOption->id)->update($insertData);

                    Logger::info("Configurable Options Pricing for product #{$whmcsProduct->id} has been updated");
                }
            }
        }
        catch(\Throwable $exception)
        {
            Logger::error('ProductPricingUpdate Cron Error: '. $exception->getMessage());
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