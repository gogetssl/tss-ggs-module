<?php

namespace TSSGGSModule\Server\app\hooks;


/**
 * Additional SANs cost their full price, not WHMCS's prorated one (App\Libs\Billing\UpgradePricing): the summary the client
 * confirms, then the upgrade invoice
 *
 * WHMCS hook ClientAreaPageUpgrade (registered in hooks.php).
 */
class UpgradeSummary
{
    public static function handle($vars)
    {
        try
        {
            $step = isset($_POST['step']) ? (int)$_POST['step'] : 0;

            return $step === 2 ? \TSSGGSModule\App\Libs\Billing\UpgradePricing::summary($vars) : [];
        }
        catch(\Throwable $e)
        {
            logActivity('TSSGGSModule: SAN upgrade price could not be shown: ' . $e->getMessage());

            return [];
        }
    }
}
