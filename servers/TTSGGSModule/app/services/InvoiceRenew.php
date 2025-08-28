<?php

namespace ModulesGarden\Servers\TTSGGSModule\app\services;


use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Invoice;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\InvoiceItem;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Service;

class InvoiceRenew {

    public function createAutoInvoice($serviceId)
    {
        $invoiceId = $this->checkInvoiceAlreadyCreated($serviceId);
        if($invoiceId === false)
        {
            return $this->createInvoice($serviceId);
        }
        return false;
    }

    public function checkInvoiceAlreadyCreated($serviceId) {

        $invoice = InvoiceItem::select('tblinvoices.*')->where('relid', $serviceId)
            ->join('tblinvoices', 'tblinvoiceitems.invoiceid', '=', 'tblinvoices.id')
            ->where('tblinvoiceitems.type', 'Hosting')
            ->where('tblinvoices.status', 'Unpaid')
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
        $daysBefore = 0;
        if($billingCycle != 'One Time' && $billingCycle != 'Free Account')
        {
            $endDate = date('Y-m-d', strtotime("+{$cycles[$billingCycle]} months", strtotime($service['nextduedate'])));
            $itemAmount = $service['amount'];
            $invoiceItemDescription = $service['name'] . ($service['domain'] ? ' - ' . $service['domain'] : '' ) . ' (' . $service['nextduedate'] . ' - ' . $endDate . ') - Renewal';
            $daysBefore = $configuration['sslSettings']['recurringDaysBeforeExpiry'];
        }

        $invoiceDate = date('Y-m-d');
        $invoiceDueDate = date('Y-m-d', strtotime("+{$daysBefore} days", strtotime($invoiceDate)));

        $results = localAPI('CreateInvoice', [
            'userid' => $service['userid'],
            'sendinvoice' => true,
            'date' => date('Y-m-d'),
            'duedate' => $invoiceDueDate,
            'itemdescription1' => $invoiceItemDescription,
            'itemamount1' => $itemAmount,
            'itemtaxed1' => $service['tax']
        ]);

        $invoiceId = $results['invoiceid'];
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