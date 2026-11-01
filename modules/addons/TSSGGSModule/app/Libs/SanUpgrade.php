<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Models\Request;
use WHMCS\Database\Capsule;

/**
 * SAN configurable options upgraded after the order: the difference is bought on the issued certificate (add-san),
 * a downgrade of an ordered certificate is refused.
 */
class SanUpgrade
{
    const REQUEST_NAME = 'purchasedSans';
    const OPTIONS      = ['single' => 'sans', 'wildcard' => 'sans_wildcard'];

    /**
     * SANs paid for the service: included in the product + bought with the configurable options
     */
    public static function paidQuantities(array $params)
    {
        return [
            'single'   => (int)($params['configoption7'] ?? 0) + (int)($params['configoptions']['sans'] ?? 0),
            'wildcard' => (int)($params['configoption8'] ?? 0) + (int)($params['configoptions']['sans_wildcard'] ?? 0),
        ];
    }

    /**
     * Same as paidQuantities() read from the database (the hook has no module params)
     */
    public static function currentQuantities($serviceId)
    {
        $service = Capsule::table('tblhosting')
            ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->where('tblhosting.id', (int)$serviceId)
            ->first(['tblproducts.configoption7', 'tblproducts.configoption8']);

        $result = ['single' => (int)($service->configoption7 ?? 0), 'wildcard' => (int)($service->configoption8 ?? 0)];
        foreach(self::optionQuantities($serviceId) as $type => $row)
        {
            $result[$type] += (int)$row->qty;
        }

        return $result;
    }

    /**
     * ['single' => row, 'wildcard' => row] of the service's SAN options (configid, qty)
     */
    protected static function optionQuantities($serviceId)
    {
        $rows = Capsule::table('tblhostingconfigoptions')
            ->join('tblproductconfigoptions', 'tblproductconfigoptions.id', '=', 'tblhostingconfigoptions.configid')
            ->where('tblhostingconfigoptions.relid', (int)$serviceId)
            ->get(['tblhostingconfigoptions.configid', 'tblhostingconfigoptions.qty', 'tblproductconfigoptions.optionname']);

        $result = [];
        foreach($rows as $row)
        {
            $systemName = explode('|', (string)$row->optionname)[0];
            $type       = array_search($systemName, self::OPTIONS, true);
            if($type !== false)
            {
                $result[$type] = $row;
            }
        }

        return $result;
    }

    public static function rememberPurchased($serviceId, array $quantities)
    {
        Request::updateOrInsert(
            ['serviceid' => (int)$serviceId, 'name' => self::REQUEST_NAME],
            ['request' => json_encode($quantities), 'status' => 'done', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]
        );
    }

    public static function purchased($serviceId)
    {
        $row = Request::where('serviceid', (int)$serviceId)->where('name', self::REQUEST_NAME)->first();

        return $row ? (json_decode($row->request, true) ?: null) : null;
    }

    /**
     * AfterConfigOptionsUpgrade: orders the additional SANs for an ordered TLS certificate
     */
    public static function afterUpgrade($upgradeId)
    {
        $upgrade = Capsule::table('tblupgrades')->where('id', (int)$upgradeId)->first();
        if(!$upgrade || $upgrade->type !== 'configoptions' || !self::isOrderedCertificate($upgrade->relid))
        {
            return;
        }

        $serviceId = (int)$upgrade->relid;
        $current   = self::currentQuantities($serviceId);
        $purchased = self::purchased($serviceId) ?: self::quantitiesBeforeUpgrade($serviceId, $upgrade, $current);

        $single   = max(0, $current['single'] - (int)$purchased['single']);
        $wildcard = max(0, $current['wildcard'] - (int)$purchased['wildcard']);
        if(!$single && !$wildcard)
        {
            return;
        }

        try
        {
            CertificateService::addSans($serviceId, $single, $wildcard);
            self::rememberPurchased($serviceId, $current);
        }
        catch(\Throwable $e)
        {
            CertificateService::event($serviceId, 'error', 'Additional SANs could not be ordered: ' . $e->getMessage());
            GeneralSettings::activity('Additional SANs for service #' . $serviceId . ' could not be ordered - order them manually: ' . $e->getMessage());
        }
    }

    /**
     * Services ordered before the purchased SANs were remembered: tblupgrades keeps "configid=>old quantity"
     */
    protected static function quantitiesBeforeUpgrade($serviceId, $upgrade, array $current)
    {
        $before = $current;
        $parts  = explode('=>', (string)$upgrade->originalvalue);
        if(count($parts) === 2)
        {
            foreach(self::optionQuantities($serviceId) as $type => $row)
            {
                if((int)$row->configid === (int)$parts[0])
                {
                    $before[$type] = $current[$type] - (int)$row->qty + (int)$parts[1];
                }
            }
        }

        return $before;
    }

    /**
     * Error message when the posted quantities lower the SANs of an ordered certificate, '' otherwise
     */
    public static function downgradeError($serviceId, array $postedOptions)
    {
        if(!self::isOrderedCertificate($serviceId))
        {
            return '';
        }

        foreach(self::optionQuantities($serviceId) as $row)
        {
            if(isset($postedOptions[$row->configid]) && (int)$postedOptions[$row->configid] < (int)$row->qty)
            {
                return 'The number of SANs of an ordered certificate can not be lowered.';
            }
        }

        return '';
    }

    protected static function isOrderedCertificate($serviceId)
    {
        $product = Capsule::table('tblhosting')
            ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->where('tblhosting.id', (int)$serviceId)
            ->value('tblproducts.servertype');
        $ssl = CertificateService::sslOrder($serviceId);

        return $product === 'TSSGGSModule' && $ssl && !empty($ssl->remoteid)
            && CertificateService::category($serviceId) === CertificateService::CATEGORY_TLS;
    }
}
