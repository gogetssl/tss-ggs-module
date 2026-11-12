<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use WHMCS\Database\Capsule;

/**
 * Whether the Automated SSL products are on sale: at least one CaaS (ACME) product and at least one AIS product of
 * this module is imported and neither hidden nor retired. The dashboard shows the Automated SSL banner until both are.
 */
class AutomatedSsl
{
    /** @var array|null ['caas' => bool, 'ais' => bool] */
    protected static $state = null;

    /** Both kinds are enabled */
    public static function enabled(): bool
    {
        $state = self::state();

        return $state['caas'] && $state['ais'];
    }

    /** ['caas' => bool, 'ais' => bool] */
    public static function state(): array
    {
        if(self::$state === null)
        {
            self::$state = ['caas' => false, 'ais' => false];
            try
            {
                $ais = [];
                foreach(RemoteProduct::whereRaw('LOWER(category) = ?', [CertificateService::CATEGORY_AIS])->pluck('remoteId') as $id)
                {
                    $ais[] = (string)$id;
                }
                $caas = array_map('strval', AcmeHelpers::getProductIds());

                $imported = Capsule::table('tblproducts')
                    ->where('servertype', 'TSSGGSModule')
                    ->where('retired', 0)
                    ->whereIn('hidden', ['', 0, '0'])
                    ->pluck('configoption2');
                foreach($imported as $remoteId)
                {
                    $remoteId = (string)$remoteId;
                    self::$state['caas'] = self::$state['caas'] || in_array($remoteId, $caas, true);
                    self::$state['ais']  = self::$state['ais'] || in_array($remoteId, $ais, true);
                }
            }
            catch(\Throwable $e)
            {
                // products not synchronised yet: not enabled
            }
        }

        return self::$state;
    }
}
