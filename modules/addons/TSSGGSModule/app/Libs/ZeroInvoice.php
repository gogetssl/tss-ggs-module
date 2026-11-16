<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use WHMCS\Database\Capsule;

/**
 * An invoice the module creates that comes to 0.00 (a 100% promotion, a service price of 0.00, a -100% client markup)
 * is paid at once: nobody would ever pay it, so the renewal it stands for would never start.
 */
class ZeroInvoice
{
    /**
     * Marks the invoice paid when its total is 0.00 and it is still unpaid; the module's InvoicePaid handlers then
     * run as for a paid one
     * @return bool whether it was settled
     */
    public static function settle($invoiceId, $reason = '')
    {
        $invoice = Capsule::table('tblinvoices')->where('id', (int)$invoiceId)->first(['total', 'status', 'userid']);
        if(!$invoice || abs((float)$invoice->total) >= 0.005 || !in_array($invoice->status, ['Unpaid', 'Draft'], true))
        {
            return false;
        }
        if($invoice->status === 'Draft')
        {
            Capsule::table('tblinvoices')->where('id', (int)$invoiceId)->update(['status' => 'Unpaid']);
        }
        $result = localAPI('UpdateInvoice', ['invoiceid' => (int)$invoiceId, 'status' => 'Paid', 'datepaid' => date('Y-m-d H:i:s')]);
        if(($result['result'] ?? '') !== 'success')
        {
            GeneralSettings::activity('Invoice #' . (int)$invoiceId . ' (0.00) could not be marked paid: ' . ($result['message'] ?? 'unknown error'));

            return false;
        }
        run_hook('InvoicePaid', ['invoiceid' => (int)$invoiceId]);
        GeneralSettings::activity('Invoice #' . (int)$invoiceId . ' totals 0.00 and was marked paid' . ($reason !== '' ? ' (' . $reason . ')' : ''));

        return true;
    }
}
