<?php

namespace TSSGGSModule\Server\app\hooks;


/**
 * WHMCS hook InvoiceCreated (registered in hooks.php).
 */
class UpgradeInvoiceCreated
{
    public static function handle($vars)
    {
        try
        {
            \TSSGGSModule\App\Libs\Billing\UpgradePricing::syncOrder(isset($vars['invoiceid']) ? (int)$vars['invoiceid'] : 0);
        }
        catch(\Throwable $e)
        {
        }
    }
}
