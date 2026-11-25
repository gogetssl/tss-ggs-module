<?php

namespace ModulesGarden\TSSGGSModule\App\Cron;

use Exception;
use ModulesGarden\TSSGGSModule\App\Libs\Certificates\CertificateSyncHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\Gateway\SSLTrustCenterApi;
use ModulesGarden\TSSGGSModule\App\Models\CronCheck;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\App\Kernel\Console\Command;
use ModulesGarden\TSSGGSModule\App\Libs\Logs\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class Sample
 * @package ModulesGarden\TSSGGSModule\App\Cron
 */
class SSLCertificates extends Command
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
        // certificate (item) ID => status reported by the vendor
        $reported = [];

        $lastSync = (new AddonModuleRepository())->getLastCertificatesSync();
        // /services/updates?since=<unix time>: changes since the last successful run, 5 minutes overlap for changes
        // made while the previous run was going; first run looks one week back
        $since = $lastSync ? max(0, (int)$lastSync - 300) : $timestamp - 7 * 86400;

        $errorToDB = '';

        foreach ($credentials as $vendor => $credential)
        {
            try {

                $provider = strtolower($vendor);
                $sandbox  = $provider == 'tss' && $credential['OperationMode'] == 'sandbox';
                if($sandbox)
                {
                    $credential['PartnerCode'] = $credential['TestPartnerCode'];
                    $credential['AuthToken'] =  $credential['TestAuthToken'];
                }

                if(empty($credential['PartnerCode']) || empty($credential['AuthToken'])) continue;

                // sandbox credentials only work on the sandbox gateway
                $api = new SSLTrustCenterApi($vendor, $credential['PartnerCode'], $credential['AuthToken'], 'tls', $sandbox);
                $updates = $api->ordersUpdate($since);

                foreach ($updates as $update)
                {
                    // TLS only: S/MIME, code / document signing and the other orders have their own refresh below
                    if(empty($update['id']) || (isset($update['category']) && $update['category'] !== 'tls'))
                    {
                        continue;
                    }
                    $reported[(string)$update['id']] = isset($update['status']) ? (string)$update['status'] : '';
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

        // product addons and their certificate services ended outside the module's own calls (M-14)
        try
        {
            foreach(\ModulesGarden\TSSGGSModule\App\Libs\Certificates\AddonCertificates::reconcile() as $change)
            {
                Logger::info('Product addon certificates: ' . $change);
            }
        }
        catch(\Throwable $e)
        {
            $io->write('Product addon check: ' . $e->getMessage());
        }

        $certificatesQuery = CertificateSyncHelpers::getCertificatesQuery()
            ->whereIn('tblsslorders.remoteid', array_map('strval', array_keys($reported)) ?: ['0']);

        // one-time conversion: orders that still hold the order ID instead of the certificate (item) ID are never
        // reported by /services/updates - each is read once and its item ID stored (CertificateSyncHelpers)
        $legacyCertificates = CertificateSyncHelpers::itemIdConversionBatch(array_map('strval', array_keys($reported)));


        $output->write('Start checking certificates...') .

        $io->title('Progress Bar!');
        $io->progressStart($certificatesQuery->count());

        $failed  = [];
        $toCheck = array_merge($certificatesQuery->get()->toArray(), $legacyCertificates);
        foreach ($toCheck as $certificate) {

            $io->progressAdvance(1);

            try {

                // a reported order: its status decides whether the order is read (CertificateSyncHelpers);
                // an order of the item ID conversion is always read
                if(array_key_exists((string)$certificate['remoteid'], $reported))
                {
                    CertificateSyncHelpers::applyReportedStatus($certificate, $reported[(string)$certificate['remoteid']], $addonConfig);
                }
                else
                {
                    CertificateSyncHelpers::syncCertificate($certificate, $addonConfig);
                }
                CertificateSyncHelpers::clearAwaitingConfiguration((int)$certificate['id']);

            } catch (\Throwable $e) {

                // counted as handled: an order that keeps failing must not hold a place among the oldest ones for ever
                \WHMCS\Database\Capsule::table('tblsslorders')->where('serviceid', (int)$certificate['id'])->update(['updated_at' => date('Y-m-d H:i:s')]);

                // submitted, but the client never chose how to validate: the order waits, it has not failed (read less often, see CertificateSyncHelpers)
                if(SSLTrustCenterApi::isAwaitingConfiguration($e))
                {
                    CertificateSyncHelpers::markAwaitingConfiguration((int)$certificate['id']);
                    $io->write('Waiting for configuration: service #' . (int)$certificate['id']);
                    continue;
                }

                $failed[$certificate['id']] = $e->getMessage();
                $io->write('Error: ' . $e->getMessage());

                Logger::error($e->getMessage(), \ModulesGarden\TSSGGSModule\App\Libs\Support\LogContext::exception($e, ['service' => $certificate['id']]));

                continue;

            }


        }

        // certificates bought at payment and completed with their token outside WHMCS (a hosting panel plugin): the
        // services waiting for their details take those orders over (App\Libs\Certificates\IncompleteOrder)
        foreach(\ModulesGarden\TSSGGSModule\App\Libs\Certificates\IncompleteOrder::adoptReported(array_keys($reported)) as $serviceId => $message)
        {
            $failed[$serviceId] = $message;
        }
        // orders migrated from the legacy TheSSLStore module that were bought at payment: their token is read once
        foreach(\ModulesGarden\TSSGGSModule\App\Libs\Certificates\IncompleteOrder::claimMigratedBatch() as $serviceId => $message)
        {
            $failed[$serviceId] = $message;
        }

        // S/MIME and code signing orders (own order data, not in the /services/updates handling above)
        foreach(\ModulesGarden\TSSGGSModule\App\Libs\ProductLines\GatewayOrderService::TYPES as $type)
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
            $errorToDB = \ModulesGarden\TSSGGSModule\App\Libs\Support\CronErrors::summary($failed, count($toCheck));
        }

        // ACME subscriptions waiting for their certificate (were refreshed by the retired SSLCertificatesProcessing)
        foreach(\ModulesGarden\TSSGGSModule\App\Models\AcmeSubscription::where('status', 'pending')->get() as $subscription)
        {
            try
            {
                \ModulesGarden\TSSGGSModule\App\Libs\Subscriptions\AcmeHelpers::refreshAcmeCertificateDetails($subscription->service_id);
            }
            catch(\Throwable $e)
            {
                $errorToDB = $errorToDB ?: $e->getMessage();
            }
        }

        // legacy migration housekeeping: expired backup tables, log rows older than 30 days
        try
        {
            \ModulesGarden\TSSGGSModule\App\Migration\Engine::housekeeping();
        }
        catch(\Throwable $e)
        {
            $errorToDB = $errorToDB ?: $e->getMessage();
        }

        CronCheck::updateOrCreate(
            ['type' => 'SSLCertificates'],
            ['last_run' => date('Y-m-d H:i:s'), 'last_error' => $errorToDB]
        );

        $io->progressFinish();
        $io->success('Finished!');
    }
}
