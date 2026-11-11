<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared;

use ModulesGarden\TSSGGSModule\App\Models\AcmeSubscription;
use ModulesGarden\TSSGGSModule\App\Models\AisSubscription;
use WHMCS\Database\Capsule;

/**
 * Where this module's SSL orders are right now; computed once per request (pipeline panel + "needs attention").
 * ACME and AutoInstall subscriptions are left out: they have no CSR step.
 */
class Pipeline
{
    protected static $data = null;

    /** ['configure', 'validation', 'issued', 'expiring', 'unpaid', 'expired' => int] */
    public static function get(): array
    {
        if(self::$data === null)
        {
            $data = ['configure' => 0, 'validation' => 0, 'issued' => 0, 'expiring' => 0, 'unpaid' => 0, 'expired' => 0];

            $subscriptions = [];
            try
            {
                $subscriptions = array_merge(AcmeSubscription::pluck('service_id')->all(), AisSubscription::pluck('service_id')->all());
            }
            catch(\Throwable $e)
            {
                // the subscription tables do not exist before their first use
            }

            $services = Capsule::table('tblhosting')
                ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
                ->leftJoin('TSSGGSModule_Requests', function($join) {
                    // SSL orders keep their state in "certificate", S/MIME orders in "smime"
                    $join->on('TSSGGSModule_Requests.serviceid', '=', 'tblhosting.id')->whereIn('TSSGGSModule_Requests.name', ['certificate', 'smime']);
                })
                ->where('tblproducts.servertype', 'TSSGGSModule')
                ->whereNotIn('tblhosting.id', $subscriptions ?: [0])
                ->get(['tblhosting.domainstatus', 'TSSGGSModule_Requests.status']);

            foreach($services as $service)
            {
                $status = strtolower((string)$service->status);
                if($service->domainstatus === 'Pending')
                {
                    $data['unpaid']++;
                }
                elseif($status === 'expired')
                {
                    $data['expired']++;
                }
                elseif($service->domainstatus !== 'Active')
                {
                    continue;
                }
                elseif($status === '' || $status === 'renewal')
                {
                    // "renewal": an S/MIME service waiting for the next certificate order
                    $data['configure']++;
                }
                elseif(in_array($status, ['pending', 'processing', 'new'], true))
                {
                    $data['validation']++;
                }
                elseif($status === 'active')
                {
                    $data['issued']++;
                }
            }

            $data['expiring'] = (int)SalesSummary::get()['expiring'];
            self::$data       = $data;
        }

        return self::$data;
    }
}
