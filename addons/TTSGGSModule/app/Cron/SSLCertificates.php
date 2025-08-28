<?php

namespace ModulesGarden\TTSGGSModule\App\Cron;

use Exception;
use ModulesGarden\TTSGGSModule\App\Libs\SSLTrustCenterApi;
use ModulesGarden\TTSGGSModule\App\Models\CronCheck;
use ModulesGarden\TTSGGSModule\App\Models\Request;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TTSGGSModule\Core\CommandLine\AbstractCommand;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Service;
use ModulesGarden\TTSGGSModule\Packages\Logs\Support\Facades\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use WHMCS\Database\Capsule;

/**
 * Class Sample
 * @package ModulesGarden\TTSGGSModule\App\Cron
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

        $certificatesQuery = Service::select(['tblhosting.*','tblsslorders.remoteid','tblproducts.configoption1'])
            ->join('tblsslorders', 'tblsslorders.serviceid', '=', 'tblhosting.id')
            ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
            ->where('tblproducts.servertype', 'TTSGGSModule')
            ->where('tblhosting.domainstatus', 'Active')
            ->whereIn('tblsslorders.remoteid', $certificatesWhere)
            ->where('tblsslorders.status', 'Configuration Submitted');


        $output->write('Start checking certificates...') .

        $io->title('Progress Bar!');
        $io->progressStart($certificatesQuery->count());

        foreach ($certificatesQuery->get()->toArray() as $certificate) {

            $io->progressAdvance(1);

            try {

                $provider = strtolower($certificate['configoption1']);
                $credentials = $addonConfig['credentials'][$provider];

                if($provider == 'tss' && $credentials['OperationMode'] == 'sandbox')
                {
                    $credentials['PartnerCode'] = $credentials['TestPartnerCode'];
                    $credentials['AuthToken'] =  $credentials['TestAuthToken'];
                }

                $api = new SSLTrustCenterApi($certificate['configoption1'], $credentials['PartnerCode'], $credentials['AuthToken'], $certificate['configoption9']);
                $orderData = $api->getOrder($certificate['remoteid'], $certificate['id']);

                $orderFiles = [];
                if($orderData['order']['status'] == 'active') {
                    try {
                        $orderFiles = $api->getCertificateFiles($certificate['remoteid']);
                    } catch (Exception $exception) {
                        Logger::error('Get Certificate Files Error:' . $exception->getMessage(), ['service' => $certificate['id']]);
                    }
                }

                Request::where('serviceid', $certificate['id'])->where('name', 'addOrder')->update([
                    'status' => $orderData['order']['status']
                ]);

                $request = ['orderData' => $orderData, 'orderFiles' => $orderFiles];

                Request::where('serviceid', $certificate['id'])->where('name', 'certificate')->delete();
                Request::insert([
                    'serviceid' => $certificate['id'],
                    'name' => 'certificate',
                    'request' => \encrypt(json_encode($request)),
                    'status' => $orderData['order']['status'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                if($orderData['order']['status'] == 'active') {
                    $expireDate = date('Y-m-d', strtotime($orderFiles['validity']['end']));
                    $nextDueDate = $expireDate;
                    Service::where('id', $certificate['id'])->update([
                        'nextinvoicedate' => $nextDueDate,
                        'nextduedate' => $nextDueDate,
                    ]);
                }

                if($orderData['order']['status'] == 'cancelled' || $orderData['order']['status'] == 'expired' || $orderData['order']['status'] == 'filed' || $orderData['order']['status'] == 'rejected')
                {
                    Service::where('id', $certificate['id'])->update([
                        'domainstatus' => 'Cancelled'
                    ]);
                }

                Logger::info("The certificate details has been updated", ['service' => $certificate['id']]);

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