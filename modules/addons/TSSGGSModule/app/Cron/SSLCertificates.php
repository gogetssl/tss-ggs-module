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
        // /services/updates?since=<unix time>: changes since the last successful run, 5 minutes overlap for changes
        // made while the previous run was going; first run looks one week back
        $since = $lastSync ? max(0, (int)$lastSync - 300) : $timestamp - 7 * 86400;

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
                $updates = $api->ordersUpdate($since);

                foreach ($updates as $update)
                {
                    $certificatesWhere[] = $update['id'];
                }

            } catch (Exception $e) {

                $errorToDB = $e->getMessage();
                $errors    = true; // keep the last sync time, so the next run asks for these changes again
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

        // /services/updates reports certificate (item) IDs. Services configured before 1.2.4 still hold the API
        // order ID in remoteid and would never match - sync a few of the longest unsynced ones per run; the sync
        // itself migrates remoteid to the item ID (CertificateSyncHelpers::syncCertificate).
        $legacyCertificates = CertificateSyncHelpers::getCertificatesQuery()
            ->whereNotIn('tblsslorders.remoteid', $certificatesWhere ?: [0])
            ->where('tblsslorders.updated_at', '<', date('Y-m-d H:i:s', strtotime('-1 day')))
            ->orderBy('tblsslorders.updated_at', 'asc')
            ->limit(50)
            ->get()
            ->toArray();


        $output->write('Start checking certificates...') .

        $io->title('Progress Bar!');
        $io->progressStart($certificatesQuery->count());

        $failed  = [];
        $toCheck = array_merge($certificatesQuery->get()->toArray(), $legacyCertificates);
        foreach ($toCheck as $certificate) {

            $io->progressAdvance(1);

            try {

                CertificateSyncHelpers::syncCertificate($certificate, $addonConfig);

            } catch (Exception $e) {

                $failed[$certificate['id']] = $e->getMessage();
                $io->write('Error: ' . $e->getMessage());

                Logger::error($e->getMessage(), \ModulesGarden\TSSGGSModule\App\Libs\LogContext::exception($e, ['service' => $certificate['id']]));

                continue;

            }


        }

        // S/MIME and code signing orders (own order data, not in the /services/updates handling above)
        foreach(\ModulesGarden\TSSGGSModule\App\Libs\GatewayOrderService::TYPES as $type)
        {
            try
            {
                foreach($type::refreshOpen() as $serviceId => $message)
                {
                    $failed[$serviceId] = $message;
                    $io->write('Error (' . $type::LABEL . '): ' . $message);
                }
            }
            catch(Exception $e)
            {
                $io->write('Error (' . $type::LABEL . '): ' . $e->getMessage());
            }
        }

        // one line that says how many orders failed and which, instead of the message of the last one
        if($failed)
        {
            $errorToDB = \ModulesGarden\TSSGGSModule\App\Libs\CronErrors::summary($failed, count($toCheck));
        }

        CronCheck::updateOrCreate(
            ['type' => 'SSLCertificates'],
            ['last_run' => date('Y-m-d H:i:s'), 'last_error' => $errorToDB]
        );

        $io->progressFinish();
        $io->success('Finished!');
    }
}