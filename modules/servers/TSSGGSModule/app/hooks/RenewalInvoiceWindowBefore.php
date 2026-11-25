<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\hooks;

use WHMCS\Database\Capsule;

/**
 * ---- Renewal invoices: the module creates them, never WHMCS (App\Libs\Billing\RenewalPolicy) ---- //
 * WHMCS invoices a service when its next invoice date enters the generation window. Right before it generates
 * invoices, the module's recurring services have that date moved out of the window; right after, it is put back.
 * So a renewal is billed once, on the module's terms (N days before the order end, at most 30), and the dates the
 * administrator sees do not change.
 *
 * WHMCS hook PreInvoicingGenerateInvoiceItems (registered in hooks.php).
 */
class RenewalInvoiceWindowBefore
{
    public static function handle($vars)
    {
        try
        {
            // the "Create Automatic Renewal Invoice" switch decides who invoices: off, WHMCS does, as it always did -
            // except for certificates kept as a record of the legacy module (App\Libs\Certificates\RecordOnly): never invoiced
            $moduleInvoices = \ModulesGarden\TSSGGSModule\App\Libs\Billing\RenewalPolicy::invoicing(\ModulesGarden\TSSGGSModule\App\Libs\Billing\RenewalPolicy::CERTIFICATE);

            $query = Capsule::table('tblhosting')
                ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
                ->where('tblproducts.servertype', 'TSSGGSModule')
                ->whereNotIn('tblhosting.billingcycle', ['One Time', 'Free Account', 'Free'])
                ->where('tblhosting.nextinvoicedate', '>', '2000-01-01')
                ->where('tblhosting.nextinvoicedate', '<', '2099-01-01');
            if(!$moduleInvoices)
            {
                \ModulesGarden\TSSGGSModule\App\Libs\Certificates\RecordOnly::only($query);
            }
            $rows = $query->get(['tblhosting.id', 'tblhosting.nextinvoicedate']);

            $GLOBALS['tssggs_renewal_invoice_dates'] = [];
            foreach($rows as $row)
            {
                $GLOBALS['tssggs_renewal_invoice_dates'][(int)$row->id] = (string)$row->nextinvoicedate;
            }
            if($GLOBALS['tssggs_renewal_invoice_dates'])
            {
                Capsule::table('tblhosting')->whereIn('id', array_keys($GLOBALS['tssggs_renewal_invoice_dates']))->update(['nextinvoicedate' => '2099-12-31']);
            }
        }
        catch(\Throwable $e)
        {
            logActivity('TSSGGSModule renewal invoices: WHMCS invoicing could not be held back: ' . $e->getMessage());
        }
    }
}
