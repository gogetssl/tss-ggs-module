<?php

namespace ModulesGarden\TTSGGSModule\App\Cron;

use Exception;
use ModulesGarden\TTSGGSModule\App\Helpers\EmailTemplates;
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

        $certificatesQuery = Service::select(['tblhosting.*','tblsslorders.remoteid','tblproducts.configoption1'])
            ->join('tblsslorders', 'tblsslorders.serviceid', '=', 'tblhosting.id')
            ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
            ->join('TTSGGSModule_Requests', 'TTSGGSModule_Requests.serviceid', '=', 'tblhosting.id')
            ->whereIn('TTSGGSModule_Requests.status', ['pending'])
            ->where('tblproducts.servertype', 'TTSGGSModule')
            ->where('tblhosting.domainstatus', 'Active')
            ->where('tblsslorders.status', 'Configuration Submitted')
            ->groupBy('tblhosting.id');

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
                if($orderData['order']['status'] == 'active')
                {
                    try {
                        $orderFiles = $api->getCertificateFiles($certificate['remoteid']);
                    } catch (Exception $exception) {
                        Logger::error('Get Certificate Files Error:'.$exception->getMessage(), ['service' => $certificate['id']]);
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

                if($orderData['order']['status'] == 'active')
                {
                    $expireDate = date('Y-m-d', strtotime($orderFiles['validity']['end']));
                    $nextDueDate = $expireDate;
                    Service::where('id', $certificate['id'])->update([
                        'nextinvoicedate' => $nextDueDate,
                        'nextduedate' => $nextDueDate,
                    ]);

                    $serviceId = $certificate['id'];
                    $userId = $certificate['userid'];

                    $certificate = Request::getOrder($certificate['id']);

                    $filename = str_replace('.', '_', $certificate['orderData']['orderData']['common_name']['name']).'.crt';
                    $files = $certificate['orderFiles']['files'];

                    $fileContentCrt = '';
                    $fileContentCA = '';
                    foreach ($files as $file)
                    {
                        if($file['name'] == $filename)
                        {
                            $fileContentCrt = $file['content'];
                        }
                        if (strpos($file['name'], 'CA.crt') !== false)
                        {
                            $fileContentCA = $file['content'];
                        }
                    }

                    $pathAttachemts = false;
                    $checkSettings = Capsule::schema()->hasTable('tblfileassetsettings');
                    if($checkSettings !== false) {
                        $filesetting = Capsule::table('tblfileassetsettings')->where('asset_type', 'email_attachments')->first();
                        if(isset($filesetting->storageconfiguration_id) && !empty($filesetting->storageconfiguration_id))
                        {
                            $checkStorage = Capsule::schema()->hasTable('tblstorageconfigurations');
                            if($checkStorage !== false) {

                                $storage = Capsule::table('tblstorageconfigurations')->where('id', $filesetting->storageconfiguration_id)->first();
                                if(isset($storage->settings) && !empty($storage->settings))
                                {
                                    $storageData = json_decode($storage->settings, true);
                                    if(isset($storageData['local_path']) && !empty($storageData['local_path']))
                                    {
                                        $pathAttachemts = $storageData['local_path'];
                                    }
                                }
                            }
                        }
                    }


                    $attachments = [];
                    if(!empty($fileContentCrt)) {
                        if($pathAttachemts === false) {
                            $tmp_ca_code = tempnam("/tmp", "FOO");
                            $handle = fopen($tmp_ca_code, "w");
                            fwrite($handle, $fileContentCrt);
                            fclose($handle);

                            $attachments[] = array(
                                'displayname' => 'crt_certificate.crt',
                                'path' => $tmp_ca_code
                            );
                        }
                        else
                        {
                            $filetemp = $pathAttachemts.DIRECTORY_SEPARATOR.$serviceId.$userId.'_crt_certificate.crt';
                            file_exists($filetemp) or touch($filetemp);
                            file_put_contents($filetemp, $fileContentCrt);

                            $attachments[] = array(
                                'displayname' => $serviceId.$userId.'_crt_certificate.crt',
                                'filename' => $serviceId.$userId.'_crt_certificate.crt'
                            );
                        }
                    }

                    if(!empty($fileContentCA)) {
                        if($pathAttachemts === false) {
                            $tmp_crt_code = tempnam("/tmp", "FOO");
                            $handle = fopen($tmp_crt_code, "w");
                            fwrite($handle, $fileContentCA);
                            fclose($handle);

                            $attachments[] = array(
                                'displayname' => 'ca_certificate.crt',
                                'path' => $tmp_crt_code
                            );
                        }
                        else
                        {
                            $filetemp = $pathAttachemts.DIRECTORY_SEPARATOR.$serviceId.$userId.'_ca_certificate.crt';
                            file_exists($filetemp) or touch($filetemp);
                            file_put_contents($filetemp, $fileContentCA);

                            $attachments[] = array(
                                'displayname' => $serviceId.$userId.'_ca_certificate.crt',
                                'filename' => $serviceId.$userId.'_ca_certificate.crt'
                            );
                        }
                    }


                    EmailTemplates::sendEmail(EmailTemplates::SEND_CERTIFICATE_TEMPLATE, $serviceId, [
                        'domain' => $filename,
                        'ssl_certyficate' => nl2br($fileContentCA),
                        'crt_code' => nl2br($fileContentCrt),
                    ], $attachments);
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
            ['type' => 'SSLCertificatesProcessing'],
            ['last_run' => date('Y-m-d H:i:s'), 'last_error' => $errorToDB]
        );

        $io->progressFinish();
        $io->success('Finished!');
    }
}