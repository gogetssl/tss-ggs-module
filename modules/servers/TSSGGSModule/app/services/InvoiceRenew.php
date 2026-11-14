<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\services;


use ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Invoice;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\InvoiceItem;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Service;
use WHMCS\Database\Capsule;

class InvoiceRenew {

    /**
     * Renewal invoice for the cron - false when an invoice for the period already exists
     */
    public function createAutoInvoice($serviceId)
    {
        list($invoiceId, $created) = $this->getOrCreateInvoice($serviceId);

        return $created ? $invoiceId : false;
    }

    /**
     * [invoice ID, created] - the check and the creation run under a per-service lock, so the cron and a client
     * clicking "Renew" at the same moment can not both create an invoice
     */
    public function getOrCreateInvoice($serviceId)
    {
        $lock = 'tssggs_renewal_invoice_' . (int)$serviceId;
        $row  = Capsule::select('SELECT GET_LOCK(?, 10) AS l', [$lock]);
        if(empty($row) || (int)$row[0]->l !== 1)
        {
            throw new \Exception('The renewal invoice is being created, please try again in a moment');
        }

        try
        {
            $invoiceId = $this->checkInvoiceAlreadyCreated($serviceId);
            if($invoiceId !== false)
            {
                return [$invoiceId, false];
            }

            return [$this->createInvoice($serviceId), true];
        }
        finally
        {
            Capsule::select('SELECT RELEASE_LOCK(?)', [$lock]);
        }
    }

    /**
     * Invoice ID of an existing renewal invoice for the service's current period, false when there is none:
     * any unpaid invoice of the service, or a paid one whose item covers the period starting at the next due date
     */
    public function checkInvoiceAlreadyCreated($serviceId) {

        $nextDueDate = (string)Service::where('id', (int)$serviceId)->value('nextduedate');

        $invoice = InvoiceItem::select('tblinvoices.*')->where('relid', $serviceId)
            ->join('tblinvoices', 'tblinvoiceitems.invoiceid', '=', 'tblinvoices.id')
            ->where('tblinvoiceitems.type', 'Hosting')
            ->where(function($query) use ($nextDueDate) {
                $query->whereIn('tblinvoices.status', ['Unpaid', 'Payment Pending', 'Draft']);
                if($nextDueDate !== '' && $nextDueDate !== '0000-00-00')
                {
                    $query->orWhere(function($period) use ($nextDueDate) {
                        $period->where('tblinvoices.status', 'Paid')
                               ->where(function($match) use ($nextDueDate) {
                                   $match->where('tblinvoiceitems.description', 'like', '%(' . $nextDueDate . ' - %')
                                         ->orWhere('tblinvoiceitems.duedate', $nextDueDate);
                               });
                    });
                }
            })
            ->orderBy('tblinvoices.id', 'desc')
            ->first();

        if(isset($invoice->id)) return $invoice->id;

        return false;
    }


    public function createInvoice($serviceId) {

        $configuration = (new AddonModuleRepository())->getModuleConfiguration();

        $service = Service::select('tblhosting.*', 'tblproducts.name', 'tblproducts.tax')
            ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
            ->where('tblhosting.id', $serviceId)
            ->first()->toArray();

        $billingCycle = $service['billingcycle'];

        $cycles = [
            'Monthly' => 1,
            'Quarterly' => 3,
            'Semi-Annually' => 6,
            'Annually' => 12,
            'Biennially' => 24,
            'Triennially' => 36
        ];
        // a service that is billed once (or free) has no renewal price: no empty invoice, which would be paid at once
        if(!isset($cycles[$billingCycle]))
        {
            $message = 'This service is billed once and cannot be renewed here. Please place a new order.';
            try
            {
                $translated = (string)\ModulesGarden\Servers\TSSGGSModule\core\Lang::absoluteT('renew_not_recurring');
                $message    = $translated !== '' && $translated !== 'renew_not_recurring' ? $translated : $message;
            }
            catch(\Throwable $e)
            {
                // no language context (cron): the English text is used
            }
            throw new \Exception($message);
        }
        if($billingCycle != 'One Time' && $billingCycle != 'Free Account')
        {
            $endDate = date('Y-m-d', strtotime("+{$cycles[$billingCycle]} months", strtotime($service['nextduedate'])));
            $itemAmount = $service['amount'];
            $invoiceItemDescription = $service['name'] . ($service['domain'] ? ' - ' . $service['domain'] : '' ) . ' (' . $service['nextduedate'] . ' - ' . $endDate . ') - Renewal';
        }

        // due the payment term after today (App\Libs\RenewalPolicy); a term longer than the lead puts it after the
        // order end, which the settings page warns about
        $invoiceDate    = date('Y-m-d');
        $invoiceDueDate = \ModulesGarden\TSSGGSModule\App\Libs\RenewalPolicy::invoiceDueDate(\ModulesGarden\TSSGGSModule\App\Libs\RenewalPolicy::CERTIFICATE, $invoiceDate);

        $results = localAPI('CreateInvoice', [
            'userid' => $service['userid'],
            'sendinvoice' => !EmailTemplates::renewalInvoiceEmailActive(),
            'date' => date('Y-m-d'),
            'duedate' => $invoiceDueDate,
            'itemdescription1' => $invoiceItemDescription,
            'itemamount1' => $itemAmount,
            'itemtaxed1' => $service['tax']
        ]);

        if(empty($results['invoiceid']))
        {
            throw new \Exception('The renewal invoice could not be created' . (!empty($results['message']) ? ': ' . $results['message'] : ''));
        }
        $invoiceId = $results['invoiceid'];
        EmailTemplates::sendRenewalInvoiceEmail($serviceId, $invoiceId);
        \ModulesGarden\TSSGGSModule\App\Libs\ProductCatalog::noticeIfDiscontinued($serviceId);
        InvoiceItem::where('invoiceid', $invoiceId)->update(['type' => 'Hosting', 'relid' => $serviceId]);
        $invoiceData = Invoice::where('id', $invoiceId)->first()->toArray();

        if($invoiceData['total'] == '0.00')
        {
            Invoice::where('id', $invoiceId)->update(['status' => 'Unpaid']);
            localAPI('UpdateInvoice', ['invoiceid' => $invoiceId, 'status' => 'Paid']);
            run_hook("InvoicePaid", ["invoiceid" => $invoiceId]);
        }

        return $invoiceId;
    }




}