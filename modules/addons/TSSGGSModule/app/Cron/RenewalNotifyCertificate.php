<?php

namespace ModulesGarden\TSSGGSModule\App\Cron;

use Exception;
use ModulesGarden\Servers\TSSGGSModule\app\services\InvoiceRenew;
use ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates;
use ModulesGarden\TSSGGSModule\App\Libs\AcmeHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\AutoInstallHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\Models\AcmeSubscription;
use ModulesGarden\TSSGGSModule\App\Models\CronCheck;
use ModulesGarden\TSSGGSModule\App\Models\Request;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\Core\CommandLine\AbstractCommand;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Helper;
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

    /** CronsCheck row that remembers the day the reminder e-mails were sent */
    const REMINDERS_MARKER = 'RenewalNotifyReminders';

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

        // renewal invoices: from N days before the order end (App\Libs\RenewalPolicy); the module is the only one
        // that creates them, whichever way a paid renewal is placed
        $auto_renew_invoice_reccuring           = \ModulesGarden\TSSGGSModule\App\Libs\RenewalPolicy::invoicing(\ModulesGarden\TSSGGSModule\App\Libs\RenewalPolicy::CERTIFICATE);
        $send_expiration_notification_reccuring = $sslSettings['recurringSendExpirationNotifications'];

        $daysNotify = explode(',', $send_expiration_notification_reccuring);

        // SSL orders stay "Configuration Submitted" when issued; an issued S/MIME order is "Completed"
        $certificatesQuery = Service::select(['tblhosting.*', 'tblsslorders.remoteid', 'tblproducts.configoption1'])
                                    ->join('tblsslorders', 'tblsslorders.serviceid', '=', 'tblhosting.id')
                                    ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
                                    ->where('tblproducts.servertype', 'TSSGGSModule')
                                    ->where('tblhosting.domainstatus', 'Active')
                                    ->where(function($query) {
                                        $query->where('tblsslorders.status', 'Configuration Submitted')
                                              ->orWhere(function($smime) {
                                                  $smime->whereIn('tblproducts.configoption9', \ModulesGarden\TSSGGSModule\App\Libs\GatewayOrderService::categories())
                                                        ->where('tblsslorders.status', 'Completed');
                                              });
                                    });
        // certificates kept as a record of the legacy module are never renewed (App\Libs\RecordOnly)
        \ModulesGarden\TSSGGSModule\App\Libs\RecordOnly::exclude($certificatesQuery);


        $output->write('Start checking certificates...') .
        $io->title('Progress Bar!');
        $io->progressStart($certificatesQuery->count());

        $packageLists = [];
        $serviceIDs   = [];

        // a run that ended with an error is repeated the same day: the reminders of that day are sent once only
        // (the services whose reminder failed are kept in the marker and are tried again)
        $today          = date('Y-m-d');
        $remindersDone  = CronCheck::where('type', self::REMINDERS_MARKER)->first();
        $remindersToday = $remindersDone && substr((string)$remindersDone->last_run, 0, 10) === $today;
        $retryIds       = $remindersToday ? array_map('intval', (array)json_decode((string)$remindersDone->last_error, true)) : [];
        $failedIds      = [];

        foreach($certificatesQuery->get() as $srv)
        {
            // subscriptions have their own renewal (SubscriptionRenewals)
            if(\ModulesGarden\TSSGGSModule\App\Libs\SubscriptionBilling::isSubscriptionProduct($srv->packageid))
            {
                continue;
            }
            // S/MIME: its own expiry e-mail, no reissue reminder (a renewal is a new order)
            $gatewayType = \ModulesGarden\TSSGGSModule\App\Libs\GatewayOrderService::typeOfProduct($srv->packageid);
            $isSmime     = $gatewayType !== null;

            $io->progressAdvance(1);

            $remind   = !$remindersToday || in_array((int)$srv->id, $retryIds, true);
            $reminded = false;
            try
            {

                $certificate = $isSmime ? [] : Request::getOrder($srv->id);

                $daysLeft    = $this->checkOrderExpireDate($srv->nextduedate);
                $daysReissue = $isSmime ? false : $this->checkReissueDate($srv->id, $certificate);

                if($remind && $srv->domainstatus == 'Active' && $daysReissue == '30' && !AutoInstallHelpers::isAutoReissueActive($srv->id))
                {
                    $output->write('Send Email Reissue: ' . $srv->id . ' - ' . $srv->domain);
                    EmailTemplates::sendEmail(EmailTemplates::REISSUE_TEMPLATE, $srv->id);
                    $reminded = true;
                    Logger::info("The notification reissue has been send", ['service' => $srv->id]);
                }

                if($daysLeft >= 0 && $remind)
                {
                    if($srv->billingcycle != 'One Time' && is_array($daysNotify) && in_array($daysLeft, $daysNotify))
                    {
                        $output->write('Send Email Expiration: ' . $srv->id . ' - ' . $srv->domain);
                        // code signing and S/MIME have their own expiry text; a CodeGuard licence is not a certificate,
                        // so it gets the general service expiry e-mail
                        $expiryTemplate = EmailTemplates::EXPIRATION_TEMPLATE;
                        if($gatewayType === \ModulesGarden\TSSGGSModule\App\Libs\CodeSigningService::class)
                        {
                            $expiryTemplate = EmailTemplates::CS_EXPIRATION_TEMPLATE;
                        }
                        elseif($gatewayType === \ModulesGarden\TSSGGSModule\App\Libs\SmimeService::class)
                        {
                            $expiryTemplate = EmailTemplates::SMIME_EXPIRATION_TEMPLATE;
                        }
                        EmailTemplates::sendEmail($expiryTemplate, $srv->id, ["expireDaysLeft" => $daysLeft]);
                        $reminded = true;
                        Logger::info("The notification expire has been send", ['service' => $srv->id]);
                    }
                }

                // the service falls due on the order end: its renewal invoice is created from N days before it, on or
                // after that day (a day without a cron run loses nothing; an invoice that exists is not made again)
                if($auto_renew_invoice_reccuring && !in_array($srv->billingcycle, ['One Time', 'Free Account'], true)
                    && \ModulesGarden\TSSGGSModule\App\Libs\RenewalPolicy::invoiceDue(\ModulesGarden\TSSGGSModule\App\Libs\RenewalPolicy::CERTIFICATE, $srv->nextduedate))
                {
                    $packageLists[$srv->packageid][] = $srv;
                    $serviceIDs[]                    = $srv->id;
                }

            }
            catch(\Throwable $e)
            {

                // a service that failed before its reminder went out gets it on the next run of the day
                if($remind && !$reminded)
                {
                    $failedIds[] = (int)$srv->id;
                }
                $errorToDB = $e->getMessage();
                Logger::error("The notification error " . $e->getMessage(), \ModulesGarden\TSSGGSModule\App\Libs\LogContext::exception($e, ['service' => $srv->id]));

            }
        }

        CronCheck::updateOrCreate(['type' => self::REMINDERS_MARKER], ['last_run' => $today . ' ' . date('H:i:s'), 'last_error' => $failedIds ? json_encode($failedIds) : '']);

        if($serviceIDs)
        {
            $output->write('Invoice Checking...');

            $classExist = class_exists('\ModulesGarden\Servers\TSSGGSModule\app\services\InvoiceRenew');
            if($classExist === false)
            {
                require_once dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR . 'servers' . DIRECTORY_SEPARATOR . 'TSSGGSModule' . DIRECTORY_SEPARATOR . 'Loader.php';
                new \ModulesGarden\Servers\TSSGGSModule\Loader();
            }

            $invoice = new \ModulesGarden\Servers\TSSGGSModule\app\services\InvoiceRenew();
            foreach($serviceIDs as $serviceID)
            {
                try
                {

                    $invoiceId = $invoice->createAutoInvoice($serviceID);
                    Logger::info("The renewal invoice has been issued for service (#$serviceID)", ['invoice' => $invoiceId]);

                }
                catch(\Throwable $e)
                {

                    $errorToDB = $e->getMessage();
                    Logger::error("The renewal invoice generate error " . $e->getMessage(), \ModulesGarden\TSSGGSModule\App\Libs\LogContext::exception($e, ['service' => $serviceID]));

                }
            }
        }

        // subscriptions (ACME and Plan + Automate): renewal invoices, unpaid renewals, auto-renewal turned off
        $io->progressFinish();
        try
        {
            foreach(\ModulesGarden\TSSGGSModule\App\Libs\SubscriptionRenewals::run($sslSettings) as $serviceId => $error)
            {
                $errorToDB = $error;
                Logger::error('Subscription renewal error ' . $error, ['service' => $serviceId]);
            }
        }
        catch(\Throwable $e)
        {
            $errorToDB = $e->getMessage();
            Logger::error('Subscription renewal error ' . $e->getMessage(), \ModulesGarden\TSSGGSModule\App\Libs\LogContext::exception($e));
        }

        // each step on its own: the time of this run is always saved, or the job would start again every 5 minutes
        foreach(['terminateUnpaid', 'closeRenewed'] as $step)
        {
            try
            {
                $this->$step();
            }
            catch(\Throwable $e)
            {
                $errorToDB = $e->getMessage();
                Logger::error('RenewalNotifyCertificate ' . $step . ' error ' . $e->getMessage(), \ModulesGarden\TSSGGSModule\App\Libs\LogContext::exception($e));
            }
        }

        CronCheck::updateOrCreate(
            ['type' => 'RenewalNotifyCertificate'],
            ['last_run' => date('Y-m-d H:i:s'), 'last_error' => $errorToDB]
        );

    }

    private function terminateUnpaid()
    {
        \ModulesGarden\TSSGGSModule\App\Libs\UnpaidTermination::run();
    }

    /**
     * Renewals on a new order: the renewed service stopped being billed when the new one took over, and is marked
     * Completed once its own order has ended (its certificate stayed downloadable until then). The order end comes
     * from the stored order - WHMCS moved the due date on when the renewal invoice was paid.
     */
    private function closeRenewed()
    {
        $today = date('Y-m-d');
        foreach(Request::where('name', 'renewedTo')->get(['serviceid', 'request']) as $row)
        {
            $serviceId = (int)$row->serviceid;
            if(\WHMCS\Database\Capsule::table('tblhosting')->where('id', $serviceId)->value('domainstatus') !== 'Active')
            {
                continue;
            }
            $stored = \ModulesGarden\TSSGGSModule\App\Libs\CertificateService::stored($serviceId);
            $end    = \ModulesGarden\TSSGGSModule\App\Libs\SslProductHelpers::getServiceDueDate((array)($stored['orderData'] ?? []));
            if($end && $end < $today)
            {
                \WHMCS\Database\Capsule::table('tblhosting')->where('id', $serviceId)->update(['domainstatus' => 'Completed']);
                \ModulesGarden\TSSGGSModule\App\Libs\CertificateService::event($serviceId, 'info', '[Renewal] Order ended on ' . $end . ': service completed, renewed as service #' . (int)$row->request);
            }
        }
    }

    public function checkOrderExpireDate($expireDate)
    {
        if(stripos($expireDate, ':') === false)
        {
            $expireDate .= ' 23:59:59';
        }
        $expire = new \DateTime($expireDate);
        $today  = new \DateTime();

        $diff = $expire->diff($today, false);
        if($diff->invert == 0)
        {
            return -1;
        }

        // the real number of days: the callers match it against their own settings (e.g. an invoice 14 or 21 days before)
        return (int)$diff->days;
    }

    public function checkReissueDate($serviceid, $certificate)
    {
        if(isset($certificate['orderFiles']['validity']['end']) && !empty($certificate['orderFiles']['validity']['end']))
        {
            $now         = strtotime(date('Y-m-d'));
            $end_date    = strtotime($certificate['orderFiles']['validity']['end']);
            $datediff    = $now - $end_date;
            $nextReissue = abs(round($datediff / (60 * 60 * 24)));
            return $nextReissue;
        }
        return false;
    }
}
