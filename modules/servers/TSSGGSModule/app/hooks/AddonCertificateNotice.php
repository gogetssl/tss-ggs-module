<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\hooks;


/**
 * A product addon's certificate service (M-14): its page says which addon it is billed with, so the client does not
 * look for renewal or cancellation on the free service
 *
 * WHMCS hook ClientAreaProductDetailsOutput (registered in hooks.php).
 */
class AddonCertificateNotice
{
    public static function handle($vars)
    {
        try
        {
            $serviceId = (int)($vars['service']->id ?? ($vars['serviceid'] ?? 0));
            if($serviceId <= 0 || !class_exists('\ModulesGarden\TSSGGSModule\App\Libs\Certificates\AddonCertificates'))
            {
                return '';
            }
            $notice = \ModulesGarden\TSSGGSModule\App\Libs\Certificates\AddonCertificates::clientNotice($serviceId);

            return $notice !== '' ? '<div class="alert alert-info" style="text-align:left">' . htmlspecialchars($notice) . '</div>' : '';
        }
        catch(\Throwable $e)
        {
            return '';
        }
    }
}
