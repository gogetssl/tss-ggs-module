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

        $auto_renew_invoice_reccuring           = (bool)$sslSettings['recurringCreateAutomaticRenewalInvoice'];
        $renew_new_order                        = (bool)$sslSettings['renewOrderViaExistingOrder'];
        $renew_invoice_days_reccuring           = $sslSettings['recurringDaysBeforeExpiry'];
        $send_expiration_notification_reccuring = $sslSettings['recurringSendExpirationNotifications'];

        $daysNotify = explode(',', $send_expiration_notification_reccuring);

        $certificatesQuery = Service::select(['tblhosting.*', 'tblsslorders.remoteid', 'tblproducts.configoption1'])
                                    ->join('tblsslorders', 'tblsslorders.serviceid', '=', 'tblhosting.id')
                                    ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
                                    ->where('tblproducts.servertype', 'TSSGGSModule')
                                    ->where('tblhosting.domainstatus', 'Active')
                                    ->where('tblsslorders.status', 'Configuration Submitted');


        $output->write('Start checking certificates...') .
        $io->title('Progress Bar!');
        $io->progressStart($certificatesQuery->count());

        $packageLists = [];
        $serviceIDs   = [];

        foreach($certificatesQuery->get() as $srv)
        {
            if(AcmeHelpers::isAcmeWhmcsProduct($srv->packageid))
            {
                continue;
            }

            $io->progressAdvance(1);

            try
            {

                $certificate = Request::getOrder($srv->id);

                $daysLeft    = $this->checkOrderExpireDate($srv->nextduedate);
                $daysReissue = $this->checkReissueDate($srv->id, $certificate);

                if($srv->domainstatus == 'Active' && $daysReissue == '30' && !AutoInstallHelpers::isAutoReissueActive($srv->id))
                {
                    $output->write('Send Email Reissue: ' . $srv->id . ' - ' . $srv->domain);
                    EmailTemplates::sendEmail(EmailTemplates::REISSUE_TEMPLATE, $srv->id);
                    Logger::info("The notification reissue has been send", ['service' => $srv->id]);
                }

                if($daysLeft >= 0)
                {
                    if($srv->billingcycle != 'One Time' && is_array($daysNotify) && in_array($daysLeft, $daysNotify))
                    {
                        $output->write('Send Email Expiration: ' . $srv->id . ' - ' . $srv->domain);
                        EmailTemplates::sendEmail(EmailTemplates::EXPIRATION_TEMPLATE, $srv->id, ["expireDaysLeft" => $daysLeft]);
                        Logger::info("The notification expire has been send", ['service' => $srv->id]);
                    }
                }

                $savedRenewDays = $renew_invoice_days_reccuring;
                if($daysLeft == (int)$savedRenewDays)
                {
                    if($srv->billingcycle != 'One Time' && $auto_renew_invoice_reccuring)
                    {
                        $packageLists[$srv->packageid][] = $srv;
                        $serviceIDs[]                    = $srv->id;
                    }
                }

            }
            catch(\Exception $e)
            {

                $errorToDB = $e->getMessage();
                Logger::error("The notification error " . $e->getMessage(), ['service' => $srv->id]);

            }
        }

        if(!$renew_new_order)
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
                catch(\Exception $e)
                {

                    $errorToDB = $e->getMessage();
                    Logger::error("The renewal invoice generate error " . $e->getMessage(), ['service' => $serviceID]);

                }
            }
        }

        //create acme invoices
        $io->progressFinish();
        $acmeSubscriptions = AcmeSubscription::whereIn('status', ['active', 'pending'])->get();

        $io->title('Subscriptions');
        $io->progressStart($acmeSubscriptions->count());

        foreach($acmeSubscriptions as $acmeSubscription)
        {
            $io->progressAdvance(1);

            if(!$acmeSubscription->auto_renew)
            {
                continue;
            }

            $service = Service::find($acmeSubscription->service_id);

            if(!$service)
            {
                continue;
            }

            if($sslSettings['recurringCreateAutomaticRenewalInvoiceSubscription'])
            {
                $acmeDaysBefore  = $sslSettings['recurringDaysBeforeExpirySubscription'];
                $nextInvoiceDate = AcmeHelpers::getAcmeNextInvoiceDate($acmeSubscription->renewal_date, $acmeDaysBefore);

                if($nextInvoiceDate !== null && $nextInvoiceDate === date('Y-m-d'))
                {
                    $invoiceCreated = AcmeHelpers::createAcmeSubscriptionRenewalInvoice($service, $service->product, $acmeSubscription);

                    if($invoiceCreated && $sslSettings['recurringSendExpirationNotificationsSubscription'])
                    {
                        EmailTemplates::sendEmail(EmailTemplates::SUBSCRIPTION_EXPIRATION_TEMPLATE, $service->id, []);
                    }

                }
            }
        }

        $io->progressFinish();

        $this->cancelOverdueAcmeSubscriptions();
        $this->cancelExpiredStoppedAutoRenewAcmeSubscriptions();

        CronCheck::updateOrCreate(
            ['type' => 'RenewalNotifyCertificate'],
            ['last_run' => date('Y-m-d H:i:s'), 'last_error' => $errorToDB]
        );

    }

    public function checkOrderExpireDate($expireDate)
    {
        $expireDaysNotify = array_flip(['90', '60', '30', '15', '10', '7', '3', '1', '0']);

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

        return isset($expireDaysNotify[$diff->days]) ? $diff->days : -1;
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

    private function cancelOverdueAcmeSubscriptions()
    {
        $today = date('Y-m-d');

        $overdueItems = Capsule::table('tblinvoiceitems')
                               ->select(['tblinvoiceitems.relid as service_id', 'tblinvoices.id as invoice_id', 'tblinvoices.duedate'])
                               ->join('tblinvoices', 'tblinvoices.id', '=', 'tblinvoiceitems.invoiceid')
                               ->join('tblhosting', 'tblhosting.id', '=', 'tblinvoiceitems.relid')
                               ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
                               ->whereIn('tblproducts.configoption2', AcmeHelpers::getProductIds())
                               ->whereIn('tblinvoices.status', ['Unpaid', 'Payment Pending'])
                               ->where('tblproducts.servertype', 'TSSGGSModule')
                               ->where('tblinvoiceitems.description', 'LIKE', '%ACME Subscription Renewal')
                               ->where('tblinvoices.duedate', '<', $today)
                               ->get();

        foreach ($overdueItems as $row)
        {
            $subscription = AcmeSubscription::getByServiceId($row->service_id);

            if (!$subscription || (int)$subscription->acme_id <= 0)
            {
                continue;
            }

            if (in_array($subscription->status, ['cancelled', 'terminated'], true))
            {
                continue;
            }

            try
            {
                $api = Helpers::getApiByServiceId($row->service_id);
                $api->cancelItem((int) $subscription->acme_id, 'Overdue renewal invoice', 'caas');
            }
            catch (\Exception $e)
            {
            }

            $subscription->status = 'cancelled';
            $subscription->cancelled_at = date('Y-m-d H:i:s');
            $subscription->save();

            Capsule::table('tblhosting')->where('id', $row->service_id)->update([
                                                                                    'domainstatus' => 'Terminated'
                                                                                ]);
        }
    }

    private function cancelExpiredStoppedAutoRenewAcmeSubscriptions()
    {
        $today = date('Y-m-d');

        $subscriptions = Capsule::table('TSSGGSModule_AcmeSubscriptions')
                                ->join('tblhosting', 'tblhosting.id', '=', 'TSSGGSModule_AcmeSubscriptions.service_id')
                                ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
                                ->whereIn('tblproducts.configoption2', AcmeHelpers::getProductIds())
                                ->where('TSSGGSModule_AcmeSubscriptions.auto_renew', '=', 0)
                                ->whereNotIn('TSSGGSModule_AcmeSubscriptions.status', ['cancelled', 'terminated'])
                                ->where('tblproducts.servertype', 'TSSGGSModule')
                                ->where(function($query) use ($today) {
                                    $query->whereNotNull('TSSGGSModule_AcmeSubscriptions.renewal_date')
                                          ->where('TSSGGSModule_AcmeSubscriptions.renewal_date', '<=', $today)
                                          ->orWhere(function($q) use ($today) {
                                              $q->whereNull('TSSGGSModule_AcmeSubscriptions.renewal_date')
                                                ->whereNotNull('TSSGGSModule_AcmeSubscriptions.period_end')
                                                ->where('TSSGGSModule_AcmeSubscriptions.period_end', '<=', $today);
                                          });
                                })
                                ->get(['TSSGGSModule_AcmeSubscriptions.*']);

        foreach ($subscriptions as $subscriptionData)
        {
            if ((int) $subscriptionData->acme_id > 0)
            {
                try
                {
                    $api = Helpers::getApiByServiceId($subscriptionData->service_id);
                    $api->cancelItem((int) $subscriptionData->acme_id, 'Auto-renew disabled and subscription expired', 'caas');
                }
                catch (\Exception $e)
                {
                }
            }

            $subscription = AcmeSubscription::find($subscriptionData->id);

            if($subscription)
            {
                $subscription->status = 'cancelled';
                $subscription->cancelled_at = date('Y-m-d H:i:s');
                $subscription->save();
            }

            Capsule::table('tblhosting')->where('id', (int) $subscriptionData->service_id)->update([
                                                                                                   'domainstatus' => 'Cancelled'
                                                                                               ]);
        }
    }
}