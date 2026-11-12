<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use WHMCS\Database\Capsule;

/**
 * A service whose expiry (next due) date has passed without a payment is terminated and the order / subscription is
 * cancelled at the vendor, so the reseller is not billed for a certificate the client did not pay for.
 * When the vendor can not be reached the service stays open and the next cron run tries again.
 */
class UnpaidTermination
{
    /**
     * @return int number of services terminated
     */
    public static function run()
    {
        if(!GeneralSettings::enabled('terminateUnpaidOnExpiry'))
        {
            return 0;
        }

        $services = Capsule::table('tblhosting')
            ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->where('tblproducts.servertype', 'TSSGGSModule')
            ->whereIn('tblhosting.domainstatus', ['Active', 'Suspended'])
            ->whereNotIn('tblhosting.billingcycle', ['One Time', 'Free Account'])
            ->where('tblhosting.nextduedate', '>', '2000-01-01')
            ->where('tblhosting.nextduedate', '<', date('Y-m-d'))
            ->get(['tblhosting.id', 'tblhosting.nextduedate']);

        $done = 0;
        foreach($services as $service)
        {
            $serviceId = (int)$service->id;
            try
            {
                // the due date can lie N days before the end (renewal rules): the service ends with the certificate / subscription
                $stored = CertificateService::stored($serviceId);
                $offset = SslProductHelpers::dueDateOffset(isset($stored['orderData']) ? (array)$stored['orderData'] : []);
                if(date('Y-m-d', strtotime('+' . $offset . ' days', strtotime($service->nextduedate))) >= date('Y-m-d'))
                {
                    continue;
                }

                if(CertificateService::category($serviceId) === CertificateService::CATEGORY_TLS)
                {
                    // not ordered at the vendor yet (waiting for the client's configuration): nothing to cancel, nothing expired
                    $ssl = CertificateService::sslOrder($serviceId);
                    if(!$ssl || empty($ssl->remoteid))
                    {
                        continue;
                    }
                }
                CertificateService::cancel($serviceId, 'Not paid by the expiry date');
            }
            catch(\Throwable $e)
            {
                GeneralSettings::activity('Service #' . $serviceId . ' expired on ' . $service->nextduedate . ' without payment but the vendor order could not be cancelled yet (retried on the next run): ' . $e->getMessage());
                continue;
            }

            self::cancelUnpaidInvoices($serviceId);
            Capsule::table('tblhosting')->where('id', $serviceId)->update(['domainstatus' => 'Terminated', 'termination_date' => date('Y-m-d')]);
            CertificateService::event($serviceId, 'info', 'Service terminated: not paid by the expiry date ' . $service->nextduedate);
            GeneralSettings::activity('Service #' . $serviceId . ' terminated: not paid by the expiry date ' . $service->nextduedate . '; the vendor order was cancelled');
            $done++;
        }

        return $done;
    }

    /**
     * Unpaid invoices that only hold this service (its renewal) are cancelled
     */
    protected static function cancelUnpaidInvoices($serviceId)
    {
        $invoiceIds = Capsule::table('tblinvoiceitems')
            ->join('tblinvoices', 'tblinvoices.id', '=', 'tblinvoiceitems.invoiceid')
            ->where('tblinvoiceitems.type', 'Hosting')
            ->where('tblinvoiceitems.relid', (int)$serviceId)
            ->whereIn('tblinvoices.status', ['Unpaid', 'Payment Pending'])
            ->pluck('tblinvoices.id');

        foreach($invoiceIds as $invoiceId)
        {
            if(Capsule::table('tblinvoiceitems')->where('invoiceid', $invoiceId)->count() === 1)
            {
                localAPI('UpdateInvoice', ['invoiceid' => (int)$invoiceId, 'status' => 'Cancelled']);
            }
        }
    }
}
