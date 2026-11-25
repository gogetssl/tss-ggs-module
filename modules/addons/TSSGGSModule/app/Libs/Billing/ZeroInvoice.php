<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Billing;

use WHMCS\Database\Capsule;
use ModulesGarden\TSSGGSModule\App\Libs\Support\GeneralSettings;

/**
 * An invoice the module creates that comes to 0.00 (a 100% promotion, a service price of 0.00, a -100% client markup)
 * is paid at once: nobody would ever pay it, so the renewal it stands for would never start.
 */
class ZeroInvoice
{
    /**
     * Marks the invoice paid when its total is 0.00 and it is still unpaid, processed by WHMCS as a paid one (the
     * service renews)
     * @return bool whether it was settled
     */
    public static function settle($invoiceId, $reason = '')
    {
        $invoice = Capsule::table('tblinvoices')->where('id', (int)$invoiceId)->first(['total', 'status', 'userid']);
        if(!$invoice || abs((float)$invoice->total) >= 0.005 || !in_array($invoice->status, ['Unpaid', 'Draft'], true))
        {
            return false;
        }
        // WHMCS's own processing of a paid invoice (what a gateway payment runs): the status, the InvoicePaid hook and
        // the invoice lines - a "Hosting" line moves the service's due date on and runs the module's Renew, exactly as
        // when the client pays. Not UpdateInvoice: WHMCS 9 sets a 0.00 invoice to Paid there but answers an error
        // ("cannot modify the subtotal of a Paid invoice") and processes nothing. No payment confirmation e-mail.
        if(!function_exists('processPaidInvoice') && defined('ROOTDIR') && is_file(ROOTDIR . '/includes/invoicefunctions.php'))
        {
            require_once ROOTDIR . '/includes/invoicefunctions.php';
        }
        if(!function_exists('processPaidInvoice'))
        {
            GeneralSettings::activity('Invoice #' . (int)$invoiceId . ' totals 0.00 but could not be marked paid (WHMCS function processPaidInvoice is missing) - mark it paid manually');

            return false;
        }
        processPaidInvoice((int)$invoiceId, true);
        if(Capsule::table('tblinvoices')->where('id', (int)$invoiceId)->value('status') !== 'Paid')
        {
            return false;
        }
        GeneralSettings::activity('Invoice #' . (int)$invoiceId . ' totals 0.00 and was marked paid' . ($reason !== '' ? ' (' . $reason . ')' : ''));

        return true;
    }
}
