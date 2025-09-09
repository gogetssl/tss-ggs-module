<?php

namespace ModulesGarden\TSSGGSModule\App\Cron;

use Exception;
use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\Models\CronCheck;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\Core\CommandLine\AbstractCommand;
use ModulesGarden\TSSGGSModule\Packages\Logs\Support\Facades\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class Sample
 * @package ModulesGarden\TSSGGSModule\App\Cron
 */
class ReSyncProducts extends AbstractCommand
{
    /**
     * Command name
     * @var string
     */
    protected $name = 'ReSyncProducts';

    /**
     * Command description
     * @var string
     */
    protected $description = 'Re-Sync Products';

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
            $checkCron = (new AddonModuleRepository())->checkCron('cron4');
            if($checkCron === false)
            {
                return;
            }

            $countryCode     = \WHMCS\Config\Setting::getValue("DefaultCountry");

            $configuredApis = Helpers::getConfiguredApis();
            foreach($configuredApis as $api)
            {
                $api->getAnnouncements(true);
                $api->getManager($countryCode, true);

            }

           Helpers::reSyncProducts();
        }
        catch(\Throwable $exception)
        {
            Logger::error('ReSyncProducts Cron Error: '. $exception->getMessage());
            $errorToDB = $exception->getMessage();
        }


        CronCheck::updateOrCreate(
            ['type' => 'ReSyncProducts'],
            ['last_run' => date('Y-m-d H:i:s'), 'last_error' => trim($errorToDB)]
        );

        $io->progressFinish();
        $io->success('Finished!');
    }
}