<?php

namespace TSSGGSModule\Server\app\hooks;


/**
 * Paid SAN upgrade of an ordered certificate - buy the additional SANs at the gateway
 *
 * WHMCS hook AfterConfigOptionsUpgrade (registered in hooks.php).
 */
class SanUpgradePurchase
{
    public static function handle($vars)
    {
        try
        {
            \TSSGGSModule\App\Libs\Billing\SanUpgrade::afterUpgrade(isset($vars['upgradeid']) ? (int)$vars['upgradeid'] : 0);
        }
        catch(\Throwable $e)
        {
            logActivity('TSSGGSModule: SAN upgrade failed - ' . $e->getMessage());
        }
    }
}
