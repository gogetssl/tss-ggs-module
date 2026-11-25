<?php

namespace TSSGGSModule\Server\app\hooks;


/**
 * while WHMCS still builds the invoice (WHMCS 9 refuses changes to a finalised one)
 *
 * WHMCS hook InvoiceCreation (registered in hooks.php).
 */
class UpgradeInvoiceCreation
{
    public static function handle($vars)
    {
        try
        {
            \TSSGGSModule\App\Libs\Billing\UpgradePricing::cleanOneTimePeriods(isset($vars['invoiceid']) ? (int)$vars['invoiceid'] : 0);
            \TSSGGSModule\App\Libs\Billing\UpgradePricing::fixInvoice(isset($vars['invoiceid']) ? (int)$vars['invoiceid'] : 0);
        }
        catch(\Throwable $e)
        {
            logActivity('TSSGGSModule: SAN upgrade invoice could not be corrected: ' . $e->getMessage());
        }
    }
}
