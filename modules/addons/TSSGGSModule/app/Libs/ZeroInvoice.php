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
        // not through UpdateInvoice: WHMCS 9 sets a 0.00 invoice to Paid there but then answers an error ("cannot
        // modify the subtotal of a Paid invoice") and runs no InvoicePaid hook. The status is set once (the WHERE
        // guards against a second run), then the module's InvoicePaid handlers run as for any paid invoice.
        $changed = Capsule::table('tblinvoices')->where('id', (int)$invoiceId)->whereIn('status', ['Unpaid', 'Draft'])
            ->update(['status' => 'Paid', 'datepaid' => date('Y-m-d H:i:s')]);
        if(!$changed)
        {
            return false;
        }
        run_hook('InvoicePaid', ['invoiceid' => (int)$invoiceId]);
        GeneralSettings::activity('Invoice #' . (int)$invoiceId . ' totals 0.00 and was marked paid' . ($reason !== '' ? ' (' . $reason . ')' : ''));

        return true;
    }
}
