<?php

namespace ModulesGarden\TSSGGSModule\App\Cron;

use Exception;
use ModulesGarden\TSSGGSModule\App\Libs\CertificateSyncHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\SSLTrustCenterApi;
use ModulesGarden\TSSGGSModule\App\Models\CronCheck;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\Core\CommandLine\AbstractCommand;
use ModulesGarden\TSSGGSModule\Packages\Logs\Support\Facades\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use WHMCS\Database\Capsule;

/**
 * Class Sample
 * @package ModulesGarden\TSSGGSModule\App\Cron
 */
class SSLCertificates extends AbstractCommand
{
    /**
     * Command name
     * @var string
     */
    protected $name = 'SSLCertificates';

    /**
     * Command description
     * @var string
     */
    protected $description = 'SSL Certificates sync.';

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
        $checkCron = (new AddonModuleRepository())->checkCron('cron2');
        if($checkCron === false)
        {
            return;
        }

        $addonConfig = (new AddonModuleRepository())->getModuleConfiguration();
        $credentials = $addonConfig['credentials'];

        $timestamp = time();
        $errors = false;
        $certificatesWhere = [];

        $lastSync = (new AddonModuleRepository())->getLastCertificatesSync();

        $errorToDB = '';

        foreach ($credentials as $vendor => $credential)
        {
            try {

                $provider = strtolower($vendor);
                if($provider == 'tss' && $credential['OperationMode'] == 'sandbox')
                {
                    $credential['PartnerCode'] = $credential['TestPartnerCode'];
                    $credential['AuthToken'] =  $credential['TestAuthToken'];
                }

                if(empty($credential['PartnerCode']) || empty($credential['AuthToken'])) continue;

                $api = new SSLTrustCenterApi($vendor, $credential['PartnerCode'], $credential['AuthToken']);
                $updates = $api->ordersUpdate();

                foreach ($updates as $update)
                {
                    $certificatesWhere[] = $update['id'];
                }

            } catch (Exception $e) {

                $errorToDB = $e->getMessage();
                $io->write('Error Check Status: ' . $e->getMessage());
                continue;

            }
        }

        if($errors === false)
        {
            (new AddonModuleRepository())->updateLastCertificatesSync($timestamp);
        }

        $certificatesQuery = CertificateSyncHelpers::getCertificatesQuery()
            ->whereIn('tblsslorders.remoteid', $certificatesWhere);


        $output->write('Start checking certificates...') .

        $io->title('Progress Bar!');
        $io->progressStart($certificatesQuery->count());

        foreach ($certificatesQuery->get()->toArray() as $certificate) {

            $io->progressAdvance(1);

            try {

                CertificateSyncHelpers::syncCertificate($certificate, $addonConfig);

            } catch (Exception $e) {

                $errorToDB = $e->getMessage();
                $io->write('Error: ' . $e->getMessage());

                Logger::error($e->getMessage(), ['service' => $certificate['id']]);

                continue;

            }


        }

        CronCheck::updateOrCreate(
            ['type' => 'SSLCertificates'],
            ['last_run' => date('Y-m-d H:i:s'), 'last_error' => $errorToDB]
        );

        $io->progressFinish();
        $io->success('Finished!');
    }
}