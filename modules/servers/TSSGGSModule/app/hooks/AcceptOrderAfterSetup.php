<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\hooks;

use WHMCS\Database\Capsule;

/**
 * Orders of products set to "set up as soon as an order is placed" / "... when the first payment is received" stay
 * Pending in WHMCS after the service was set up (a known WHMCS behaviour), so an admin has to accept each one. Once
 * the certificate service was created, the order is accepted - only when nothing else in it still needs an admin
 * (domains, addons, other services not set up yet); failed set-ups never get here.
 *
 * WHMCS hook AfterModuleCreate (registered in hooks.php).
 */
class AcceptOrderAfterSetup
{
    public static function handle($vars)
    {
        try
        {
            $params    = isset($vars['params']) && is_array($vars['params']) ? $vars['params'] : [];
            $serviceId = (int)($params['serviceid'] ?? 0);
            if(!$serviceId || ($params['moduletype'] ?? 'TSSGGSModule') !== 'TSSGGSModule')
            {
                return;
            }

            $service = Capsule::table('tblhosting as h')->join('tblproducts as p', 'p.id', '=', 'h.packageid')
                ->where('h.id', $serviceId)->first(['h.orderid', 'p.autosetup', 'p.servertype']);
            if(!$service || $service->servertype !== 'TSSGGSModule' || !in_array($service->autosetup, ['order', 'payment'], true) || !(int)$service->orderid)
            {
                return;
            }

            $orderId = (int)$service->orderid;
            if(Capsule::table('tblorders')->where('id', $orderId)->value('status') !== 'Pending')
            {
                return;
            }

            // everything else in the order is done: no domains or addons waiting, every other service already set up
            $waiting = Capsule::table('tblhosting')->where('orderid', $orderId)->where('id', '!=', $serviceId)->where('domainstatus', 'Pending')->exists()
                || Capsule::table('tbldomains')->where('orderid', $orderId)->where('status', 'Pending')->exists()
                || Capsule::table('tblhostingaddons')->where('orderid', $orderId)->where('status', 'Pending')->exists();
            if($waiting)
            {
                return;
            }

            $result = localAPI('AcceptOrder', ['orderid' => $orderId, 'autosetup' => false, 'sendemail' => false]);
            if(($result['result'] ?? '') === 'success')
            {
                \ModulesGarden\TSSGGSModule\App\Libs\Support\GeneralSettings::activity('Order #' . $orderId . ' accepted automatically: service #' . $serviceId . ' was set up (automatic setup on ' . $service->autosetup . ')');
            }
            else
            {
                \ModulesGarden\TSSGGSModule\App\Libs\Support\GeneralSettings::activity('Order #' . $orderId . ' could not be accepted automatically: ' . ($result['message'] ?? 'unknown error'));
            }
        }
        catch(\Throwable $e)
        {
            logActivity('TSSGGSModule order auto-accept: ' . $e->getMessage());
        }
    }
}
