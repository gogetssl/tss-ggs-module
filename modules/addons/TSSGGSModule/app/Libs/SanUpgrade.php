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
    const LEDGER_NAME  = 'sanLedger';

    /** extra SANs can be reduced this many days after their purchase */
    const DOWNGRADE_DAYS = 30;
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
            self::afterDowngrade($serviceId, $current, $purchased);

            return;
        }

        try
        {
            // the ledger is seeded from what was paid before this upgrade, then the new SANs are added with today's date
            $ledger = self::ledger($serviceId, $purchased);
            CertificateService::addSans($serviceId, $single, $wildcard);
            foreach(['single' => $single, 'wildcard' => $wildcard] as $type => $qty)
            {
                if($qty > 0)
                {
                    $ledger[] = ['type' => $type, 'qty' => $qty, 'date' => date('Y-m-d'), 'initial' => false];
                }
            }
            self::saveLedger($serviceId, $ledger);
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
     * SAN purchases of the service: [type single|wildcard, qty, date, initial]. "initial" are the SANs of the main
     * order (never reducible); the others were bought later and are reducible for DOWNGRADE_DAYS after their date.
     * A service without a ledger (ordered before it existed) has everything it paid for as initial.
     */
    public static function ledger($serviceId, array $paid = null)
    {
        $row = Request::where('serviceid', (int)$serviceId)->where('name', self::LEDGER_NAME)->first();
        if($row)
        {
            return json_decode($row->request, true) ?: [];
        }

        $paid    = $paid ?: (self::purchased($serviceId) ?: self::currentQuantities($serviceId));
        $regdate = (string)Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('regdate');
        $ledger  = [];
        foreach(['single', 'wildcard'] as $type)
        {
            if((int)$paid[$type] > 0)
            {
                $ledger[] = ['type' => $type, 'qty' => (int)$paid[$type], 'date' => $regdate !== '' ? $regdate : date('Y-m-d'), 'initial' => true];
            }
        }

        return $ledger;
    }

    protected static function saveLedger($serviceId, array $ledger)
    {
        Request::updateOrInsert(
            ['serviceid' => (int)$serviceId, 'name' => self::LEDGER_NAME],
            ['request' => json_encode(array_values($ledger)), 'status' => 'done', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]
        );
    }

    /**
     * SANs that can still be reduced per type: bought after the order, not older than DOWNGRADE_DAYS
     * @return array ['single' => n, 'wildcard' => n]
     */
    public static function reducible($serviceId)
    {
        $result = ['single' => 0, 'wildcard' => 0];
        $limit  = date('Y-m-d', strtotime('-' . self::DOWNGRADE_DAYS . ' days'));
        foreach(self::ledger($serviceId) as $entry)
        {
            if(empty($entry['initial']) && $entry['date'] >= $limit)
            {
                $result[$entry['type']] += (int)$entry['qty'];
            }
        }

        return $result;
    }

    /**
     * After a reduction: the newest reducible purchases are used up first
     */
    protected static function afterDowngrade($serviceId, array $current, array $purchased)
    {
        $ledger  = self::ledger($serviceId, $purchased);
        $limit   = date('Y-m-d', strtotime('-' . self::DOWNGRADE_DAYS . ' days'));
        $removed = [];
        foreach(['single', 'wildcard'] as $type)
        {
            $left = max(0, (int)$purchased[$type] - (int)$current[$type]);
            if(!$left)
            {
                continue;
            }
            $removed[$type] = $left;
            for($i = count($ledger) - 1; $i >= 0 && $left > 0; $i--)
            {
                if($ledger[$i]['type'] !== $type || !empty($ledger[$i]['initial']) || $ledger[$i]['date'] < $limit)
                {
                    continue;
                }
                $take = min($left, (int)$ledger[$i]['qty']);
                $ledger[$i]['qty'] -= $take;
                $left -= $take;
            }
        }
        if(!$removed)
        {
            return;
        }

        self::saveLedger($serviceId, array_filter($ledger, function($entry) { return (int)$entry['qty'] > 0; }));
        self::rememberPurchased($serviceId, $current);
        $text = 'SANs reduced by the client: ' . implode(', ', array_map(function($type, $qty) { return $qty . ' ' . $type; }, array_keys($removed), $removed))
            . ' - reduce them at the vendor too if it refunds extra SANs';
        CertificateService::event($serviceId, 'info', $text);
        GeneralSettings::activity('Service #' . $serviceId . ': ' . $text);
    }

    /**
     * Error message when the posted quantities lower the SANs of an ordered certificate more than allowed, '' otherwise:
     * only SANs bought after the order can be reduced, and only within DOWNGRADE_DAYS of their purchase
     */
    public static function downgradeError($serviceId, array $postedOptions)
    {
        if(!self::isOrderedCertificate($serviceId))
        {
            return '';
        }

        $reducible = self::reducible($serviceId);
        foreach(self::optionQuantities($serviceId) as $type => $row)
        {
            if(isset($postedOptions[$row->configid]) && (int)$row->qty - (int)$postedOptions[$row->configid] > $reducible[$type])
            {
                return 'You can only reduce SANs that were bought after the order and not longer than ' . self::DOWNGRADE_DAYS . ' days ago (' . $reducible[$type] . ' can be reduced now).';
            }
        }

        return '';
    }

    /**
     * @throws \Exception when the names need more SANs than the service paid for (the common name is not a SAN, neither
     *         is the base domain of a wildcard common name)
     */
    public static function assertWithinLimits($serviceId, $commonName, array $names, array $paid = null)
    {
        $paid   = $paid ?: self::currentQuantities($serviceId);
        $base   = strpos($commonName, '*.') === 0 ? substr($commonName, 2) : null;
        $single = $wildcard = 0;
        foreach($names as $name)
        {
            $name = strtolower(trim((string)$name));
            if($name === '' || $name === strtolower($commonName) || $name === $base)
            {
                continue;
            }
            strpos($name, '*.') === 0 ? $wildcard++ : $single++;
        }
        if($single > $paid['single'])
        {
            throw new \Exception('This certificate includes ' . $paid['single'] . ' additional domain name(s); ' . $single . ' were submitted.');
        }
        if($wildcard > $paid['wildcard'])
        {
            throw new \Exception('This certificate includes ' . $paid['wildcard'] . ' additional wildcard name(s); ' . $wildcard . ' were submitted.');
        }
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
