<?php

namespace ModulesGarden\TTSGGSModule\App\Cron;

use Exception;
use ModulesGarden\Servers\TTSGGSModule\app\services\InvoiceRenew;
use ModulesGarden\TTSGGSModule\App\Helpers\EmailTemplates;
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
class RenewalNotifyCertificate extends AbstractCommand
{
    /**
     * Command name
     * @var string
     */
    protected $name = 'RenewalNotifyCertificate';

    /**
     * Command description
     * @var string
     */
    protected $description = 'Renewal Notification certificate.';

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
        $checkCron = (new AddonModuleRepository())->checkCron('cron3');
        if($checkCron === false)
        {
            return;
        }

        $errorToDB = '';

        $addonConfig = (new AddonModuleRepository())->getModuleConfiguration();
        $sslSettings = $addonConfig['sslSettings'];

        $auto_renew_invoice_reccuring           = (bool) $sslSettings['recurringCreateAutomaticRenewalInvoice'];
        $renew_new_order                        = (bool) $sslSettings['renewOrderViaExistingOrder'];
        $renew_invoice_days_reccuring           = $sslSettings['recurringDaysBeforeExpiry'];
        $send_expiration_notification_reccuring = $sslSettings['recurringSendExpirationNotifications'];

        $daysNotify = explode(',', $send_expiration_notification_reccuring);

        $certificatesQuery = Service::select(['tblhosting.*','tblsslorders.remoteid','tblproducts.configoption1'])
            ->join('tblsslorders', 'tblsslorders.serviceid', '=', 'tblhosting.id')
            ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
            ->where('tblproducts.servertype', 'TTSGGSModule')
            ->where('tblhosting.domainstatus', 'Active')
            ->where('tblsslorders.status', 'Configuration Submitted');


        $output->write('Start checking certificates...') .
        $io->title('Progress Bar!');
        $io->progressStart($certificatesQuery->count());

        $packageLists = [];
        $serviceIDs   = [];

        foreach ($certificatesQuery->get() as $srv)
        {
            $io->progressAdvance(1);

            try {

                $certificate = Request::getOrder($srv->id);

                $daysLeft = $this->checkOrderExpireDate($srv->nextduedate);
                $daysReissue = $this->checkReissueDate($srv->id, $certificate);

                if ($srv->domainstatus == 'Active' && $daysReissue == '30') {
                    $output->write('Send Email Reissue: ' . $srv->id . ' - ' . $srv->domain);
                    EmailTemplates::sendEmail(EmailTemplates::REISSUE_TEMPLATE, $srv->id);
                    Logger::info("The notification reissue has been send", ['service' => $srv->id]);
                }

                if ($daysLeft >= 0) {
                    if ($srv->billingcycle != 'One Time' && is_array($daysNotify) && in_array($daysLeft, $daysNotify)) {
                        $output->write('Send Email Expiration: ' . $srv->id . ' - ' . $srv->domain);
                        EmailTemplates::sendEmail(EmailTemplates::EXPIRATION_TEMPLATE, $srv->id, ["expireDaysLeft" => $daysLeft]);
                        Logger::info("The notification expire has been send", ['service' => $srv->id]);
                    }
                }

                $savedRenewDays = $renew_invoice_days_reccuring;
                if ($daysLeft == (int)$savedRenewDays) {
                    if ($srv->billingcycle != 'One Time' && $auto_renew_invoice_reccuring) {
                        $packageLists[$srv->packageid][] = $srv;
                        $serviceIDs[] = $srv->id;
                    }
                }

            } catch (\Exception $e) {

                $errorToDB = $e->getMessage();
                Logger::error("The notification error ".$e->getMessage(), ['service' => $srv->id]);

            }
        }

        if(!$renew_new_order)
        {
            $output->write('Invoice Checking...');

            $invoice = new InvoiceRenew();
            foreach ($serviceIDs as $serviceID) {

                try {

                    $invoiceId = $invoice->createAutoInvoice($serviceID);
                    Logger::info("The renewal invoice has been issued for service (#$serviceID)", ['invoice' => $invoiceId]);

                } catch (\Exception $e) {

                    $errorToDB = $e->getMessage();
                    Logger::error("The renewal invoice generate error ".$e->getMessage(), ['service' => $serviceID]);

                }
            }
        }

        CronCheck::updateOrCreate(
            ['type' => 'RenewalNotifyCertificate'],
            ['last_run' => date('Y-m-d H:i:s'), 'last_error' => $errorToDB]
        );

    }

    public function checkOrderExpireDate($expireDate)
    {
        $expireDaysNotify = array_flip(['90', '60', '30', '15', '10', '7', '3', '1', '0']);

        if (stripos($expireDate, ':') === false)
        {
            $expireDate .= ' 23:59:59';
        }
        $expire = new \DateTime($expireDate);
        $today  = new \DateTime();

        $diff = $expire->diff($today, false);
        if ($diff->invert == 0)
        {
            return -1;
        }

        return isset($expireDaysNotify[$diff->days]) ? $diff->days : -1;
    }

    public function checkReissueDate($serviceid, $certificate)
    {
        if (isset($certificate['orderFiles']['validity']['end']) && !empty($certificate['orderFiles']['validity']['end'])) {

            $now = strtotime(date('Y-m-d'));
            $end_date = strtotime($certificate['orderFiles']['validity']['end']);
            $datediff = $now - $end_date;
            $nextReissue = abs(round($datediff / (60 * 60 * 24)));
            return $nextReissue;
        }
        return false;
    }
}