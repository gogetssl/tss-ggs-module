<?php

namespace ModulesGarden\TSSGGSModule\App\Cron;

use Exception;
use ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates;
use ModulesGarden\TSSGGSModule\App\Libs\AcmeHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\AutoInstallHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\CertificateService;
use ModulesGarden\TSSGGSModule\App\Libs\SSLTrustCenterApi;
use ModulesGarden\TSSGGSModule\App\Libs\SslProductHelpers;
use ModulesGarden\TSSGGSModule\App\Models\AcmeSubscription;
use ModulesGarden\TSSGGSModule\App\Models\CronCheck;
use ModulesGarden\TSSGGSModule\App\Models\Request;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\Core\CommandLine\AbstractCommand;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Service;
use ModulesGarden\TSSGGSModule\Packages\Logs\Support\Facades\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use WHMCS\Database\Capsule;

/**
 * Class Sample
 * @package ModulesGarden\TSSGGSModule\App\Cron
 */
class SSLCertificatesProcessing extends AbstractCommand
{
    /**
     * Command name
     * @var string
     */
    protected $name = 'SSLCertificatesProcessing';

    /**
     * Command description
     * @var string
     */
    protected $description = 'SSL Certificates Processing sync.';

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
        $checkCron = (new AddonModuleRepository())->checkCron('cron5');
        if($checkCron === false)
        {
            return;
        }

        $addonConfig = (new AddonModuleRepository())->getModuleConfiguration();

        $errorToDB = '';

        $certificatesQuery = Service::select(['tblhosting.*','tblsslorders.remoteid','tblproducts.configoption1','tblproducts.configoption9'])
            ->join('tblsslorders', 'tblsslorders.serviceid', '=', 'tblhosting.id')
            ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
            ->join('TSSGGSModule_Requests', 'TSSGGSModule_Requests.serviceid', '=', 'tblhosting.id')
            // a new order is stored as "processing" until the gateway reports it (GoGetSSL adds the validation details
            // a few seconds after the order is placed) - without it such orders were never re-read
            ->whereIn('TSSGGSModule_Requests.status', ['pending', 'processing'])
            ->where('tblproducts.servertype', 'TSSGGSModule')
            ->where('tblhosting.domainstatus', 'Active')
            ->where('tblsslorders.status', 'Configuration Submitted')
            // S/MIME and code signing orders keep their own request rows (also pending): GatewayOrderService refreshes them
            ->where(function($query) {
                $query->whereNull('tblproducts.configoption9')->orWhereNotIn('tblproducts.configoption9', array_merge(\ModulesGarden\TSSGGSModule\App\Libs\GatewayOrderService::categories(), \ModulesGarden\TSSGGSModule\App\Libs\SSLTrustCenterApi::SUBSCRIPTION_CATEGORIES));
            })
            ->groupBy('tblhosting.id');

        $output->write('Start checking certificates...') .

        $io->title('Progress Bar!');
        $io->progressStart($certificatesQuery->count());

        $failed = [];
        $totalChecked = $certificatesQuery->get()->count();
        foreach ($certificatesQuery->get()->toArray() as $certificate) {

            $io->progressAdvance(1);

            try {

                $api = SSLTrustCenterApi::forVendor($certificate['configoption1'], $certificate['configoption9']);
                $orderData = $api->getOrder($certificate['remoteid'], $certificate['id']);

                $orderFiles = [];
                if($orderData['order']['status'] == 'active')
                {
                    try {
                        $orderFiles = $api->getCertificateFiles($certificate['remoteid']);
                    } catch (Exception $exception) {
                        Logger::error('Get Certificate Files Error:'.$exception->getMessage(), \ModulesGarden\TSSGGSModule\App\Libs\LogContext::exception($exception, ['service' => $certificate['id']]));
                    }
                }

                Request::where('serviceid', $certificate['id'])->where('name', 'addOrder')->update([
                    'status' => $orderData['order']['status']
                ]);

                $request = ['orderData' => $orderData, 'orderFiles' => $orderFiles];

                $previousStatus = Request::where('serviceid', $certificate['id'])->where('name', 'certificate')->value('status');
                Request::where('serviceid', $certificate['id'])->where('name', 'certificate')->delete();
                Request::insert([
                    'serviceid' => $certificate['id'],
                    'name' => 'certificate',
                    'request' => \encrypt(json_encode($request)),
                    'status' => $orderData['order']['status'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                if($orderData['order']['status'] == 'active')
                {
                    // Subscription end (falls back to the certificate end) - certificates within the subscription are reissued
                    $nextDueDate = SslProductHelpers::getServiceDueDate($orderData, $orderFiles);
                    if($nextDueDate)
                    {
                        Service::where('id', $certificate['id'])->update([
                            'nextinvoicedate' => $nextDueDate,
                            'nextduedate' => $nextDueDate,
                        ]);
                    }
                }

                // certificate e-mail - the same rule for every sync (CertificateService::notifyIssued)
                CertificateService::notifyIssued($certificate['id'], $previousStatus, $orderData['order']['status']);
                if($orderData['order']['status'] === 'active')
                {
                    CertificateService::forgetPreviousCertificate($certificate['id']);
                }

                // same rule as the order status sync (CertificateSyncHelpers): expired / filed = Terminated with a date
                if($orderData['order']['status'] == 'cancelled' || $orderData['order']['status'] == 'rejected')
                {
                    Service::where('id', $certificate['id'])->update([
                        'domainstatus' => 'Cancelled'
                    ]);
                }
                elseif($orderData['order']['status'] == 'expired' || $orderData['order']['status'] == 'filed')
                {
                    Service::where('id', $certificate['id'])->whereIn('domainstatus', ['Pending', 'Active', 'Suspended'])->update([
                        'domainstatus'     => 'Terminated',
                        'termination_date' => date('Y-m-d'),
                    ]);
                }

                if($previousStatus !== $orderData['order']['status'])
                {
                    Logger::info('Certificate status: ' . ($previousStatus ?: 'new') . ' -> ' . $orderData['order']['status'], ['service' => $certificate['id'], 'from' => $previousStatus, 'to' => $orderData['order']['status']]);
                }

            } catch (Exception $e) {

                $failed[$certificate['id']] = $e->getMessage();
                $io->write('Error: ' . $e->getMessage());

                Logger::error($e->getMessage(), \ModulesGarden\TSSGGSModule\App\Libs\LogContext::exception($e, ['service' => $certificate['id']]));

                continue;

            }
        }

        $io->progressFinish();
        // one line with the number of failed orders and which, not the message of the last one
        if($failed)
        {
            $errorToDB = \ModulesGarden\TSSGGSModule\App\Libs\CronErrors::summary($failed, $totalChecked);
        }
        $io->title('Subscriptions');
        $pendingSubscriptions = AcmeSubscription::where('status', 'pending')->get();
        $io->progressStart($pendingSubscriptions->count());

        foreach($pendingSubscriptions as $subscription)
        {
            try
            {
                $io->progressAdvance(1);
                AcmeHelpers::refreshAcmeCertificateDetails($subscription->service_id);
            }
            catch(\Throwable $e)
            {
                $errorToDB = $e->getMessage();
            }
        }

        $io->progressFinish();
        // (the AutoInstall queue has its own job, SSLAutoInstall: it was run from here as well)

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
            ['type' => 'SSLCertificatesProcessing'],
            ['last_run' => date('Y-m-d H:i:s'), 'last_error' => $errorToDB]
        );

        $io->success('Finished!');
    }
}
