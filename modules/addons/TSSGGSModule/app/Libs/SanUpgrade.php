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
    const LAST_REISSUE = 'sanLastReissue';

    /** Settings > SSL "Additional SANs bought later": the full price for the billing cycle (default) or WHMCS's prorated one */
    const PRICING_FULL     = 'full';
    const PRICING_PRORATED = 'prorated';

    public static function pricing()
    {
        $settings = (new \ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository())->getModuleConfiguration()['sslSettings'] ?? [];

        return ($settings['sanUpgradePricing'] ?? self::PRICING_FULL) === self::PRICING_PRORATED ? self::PRICING_PRORATED : self::PRICING_FULL;
    }

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
        if(!$upgrade || $upgrade->type !== 'configoptions')
        {
            return;
        }
        // ACME: the slots are domains of the subscription, not SANs of a certificate. Nothing is ordered at the vendor
        // for a slot - it charges per domain when the client adds one to the subscription - so a paid upgrade only
        // raises the quota the client area allows.
        $packageId = (int)Capsule::table('tblhosting')->where('id', (int)$upgrade->relid)->value('packageid');
        if(AcmeHelpers::isAcmeWhmcsProduct($packageId))
        {
            return;
        }
        if(!self::isOrderedCertificate($upgrade->relid))
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
            self::afterDowngrade($serviceId, $current, $purchased, $upgrade);

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
                    // "paid": what the client paid for them (after a promotion, before tax) - the refund if given back
                    $ledger[] = ['type' => $type, 'qty' => $qty, 'date' => date('Y-m-d'), 'at' => date('Y-m-d H:i:s'), 'initial' => false,
                                 'paid' => self::paidForUpgrade($serviceId, $upgrade, $type)];
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

    /** The certificate was reissued (client, admin or automatic): SANs bought before it are no longer refundable */
    public static function noteReissue($serviceId)
    {
        try
        {
            Request::updateOrInsert(
                ['serviceid' => (int)$serviceId, 'name' => self::LAST_REISSUE],
                ['request' => date('Y-m-d H:i:s'), 'status' => 'done', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]
            );
        }
        catch(\Throwable $e)
        {
            // never break a reissue that went through at the vendor
        }
    }

    /** Date and time of the last reissue or renewal order of the certificate ('' when none) */
    public static function lastReissue($serviceId)
    {
        $reissue = (string)Request::where('serviceid', (int)$serviceId)->where('name', self::LAST_REISSUE)->value('request');
        $renewal = (string)Request::where('serviceid', (int)$serviceId)->where('name', 'renewOrder')->max('created_at');

        return max($reissue, $renewal);
    }

    /**
     * Names the certificate uses beyond the common name ['single' => n, 'wildcard' => n] (the request of the current
     * order or of its last reissue)
     */
    public static function usedNames($serviceId)
    {
        $used    = ['single' => 0, 'wildcard' => 0];
        $request = Request::currentOrder((int)$serviceId);
        $data    = $request && $request->request ? (json_decode((string)\decrypt($request->request), true) ?: []) : [];
        $common  = strtolower((string)($data['common_name']['name'] ?? ''));
        $base    = strpos($common, '*.') === 0 ? substr($common, 2) : null;
        foreach((array)($data['alternative_names'] ?? []) as $san)
        {
            $name = strtolower(trim((string)(is_array($san) ? ($san['name'] ?? '') : $san)));
            if($name === '' || $name === $common || $name === $base)
            {
                continue;
            }
            $used[strpos($name, '*.') === 0 ? 'wildcard' : 'single']++;
        }

        return $used;
    }

    /**
     * A ledger purchase that can be given back for a refund: bought after the order, within DOWNGRADE_DAYS, and the
     * certificate has not been reissued (or renewed) since
     */
    protected static function refundableEntry(array $entry, $lastReissue)
    {
        if(!empty($entry['initial']) || $entry['date'] < date('Y-m-d', strtotime('-' . self::DOWNGRADE_DAYS . ' days')))
        {
            return false;
        }
        if($lastReissue === '')
        {
            return true;
        }
        // purchases recorded before the time was kept: a reissue on the same day counts as after them
        return isset($entry['at']) ? $entry['at'] > $lastReissue : $entry['date'] > substr($lastReissue, 0, 10);
    }

    /**
     * SANs that can still be reduced per type: refundable purchases (refundableEntry), and never into names the
     * certificate uses
     * @return array ['single' => n, 'wildcard' => n]
     */
    public static function reducible($serviceId)
    {
        $result = ['single' => 0, 'wildcard' => 0];
        $last   = self::lastReissue($serviceId);
        foreach(self::ledger($serviceId) as $entry)
        {
            if(self::refundableEntry($entry, $last))
            {
                $result[$entry['type']] += (int)$entry['qty'];
            }
        }
        $paid = self::currentQuantities($serviceId);
        $used = self::usedNames($serviceId);
        foreach($result as $type => $count)
        {
            $result[$type] = max(0, min($count, $paid[$type] - $used[$type]));
        }

        return $result;
    }

    /**
     * Takes reduced SANs out of the ledger, newest refundable purchases first.
     * @return array [ledger, refund (what the client paid for them), refund known (every purchase taken has its price)]
     */
    protected static function take(array $ledger, array $remove, $lastReissue)
    {
        $refund = 0.0;
        $known  = true;
        foreach($remove as $type => $left)
        {
            for($i = count($ledger) - 1; $i >= 0 && $left > 0; $i--)
            {
                if($ledger[$i]['type'] !== $type || !self::refundableEntry($ledger[$i], $lastReissue))
                {
                    continue;
                }
                $qty  = (int)$ledger[$i]['qty'];
                $take = min($left, $qty);
                if(isset($ledger[$i]['paid']) && $qty > 0)
                {
                    $part = round((float)$ledger[$i]['paid'] * $take / $qty, 2);
                    $refund += $part;
                    $ledger[$i]['paid'] = round((float)$ledger[$i]['paid'] - $part, 2);
                }
                else
                {
                    $known = false;
                }
                $ledger[$i]['qty'] -= $take;
                $left -= $take;
            }
        }

        return [$ledger, round($refund, 2), $known];
    }

    /**
     * What the client gets back for reducing the SANs ['single' => n, 'wildcard' => n]: the price paid for them, or
     * null when it is not known (purchases made before the module recorded prices) - WHMCS's own credit then applies
     */
    public static function refundQuote($serviceId, array $remove)
    {
        list(, $refund, $known) = self::take(self::ledger($serviceId), $remove, self::lastReissue($serviceId));

        return $known ? $refund : null;
    }

    /**
     * What the client paid for the SANs of one type added by an upgrade order: its invoice line, less its share of the
     * promotion line, before tax (0.00 when WHMCS made no invoice)
     */
    protected static function paidForUpgrade($serviceId, $upgrade, $type)
    {
        $rows      = self::optionQuantities($serviceId);
        $configId  = isset($rows[$type]) ? (int)$rows[$type]->configid : 0;
        $paid      = 0.0;
        // this upgrade, or the other option's upgrade of the same order (both SAN types changed at once)
        $upgrades  = (int)$upgrade->orderid > 0
            ? Capsule::table('tblupgrades')->where('orderid', (int)$upgrade->orderid)->where('type', 'configoptions')->get()->all()
            : [$upgrade];
        foreach($upgrades as $row)
        {
            if((int)explode('=>', (string)$row->originalvalue)[0] !== $configId)
            {
                continue;
            }
            $line = Capsule::table('tblinvoiceitems')->where('type', 'Upgrade')->where('relid', (int)$row->id)->first(['invoiceid', 'amount']);
            if(!$line)
            {
                continue;
            }
            $items    = Capsule::table('tblinvoiceitems')->where('invoiceid', (int)$line->invoiceid)->pluck('amount')->all();
            $positive = array_sum(array_filter(array_map('floatval', $items), function($a) { return $a > 0; }));
            $factor   = $positive > 0 ? max(0, array_sum(array_map('floatval', $items))) / $positive : 1;
            $paid    += round((float)$line->amount * $factor, 2);
        }

        return round($paid, 2);
    }

    /**
     * After a reduction: the newest refundable purchases are taken out first; when WHMCS credits downgrades, the client
     * gets back what was paid for them (WHMCS credits a prorated amount: the difference is credited, or taken back)
     */
    protected static function afterDowngrade($serviceId, array $current, array $purchased, $upgrade = null)
    {
        // WHMCS runs the hook once per changed option: only this upgrade's SAN type is settled here (and remembered), so
        // its credit is compared with this upgrade's own WHMCS credit
        $only = $upgrade ? self::upgradeType($serviceId, $upgrade) : null;
        if($only)
        {
            $other           = $only === 'single' ? 'wildcard' : 'single';
            $current[$other] = (int)$purchased[$other];
        }
        $remove = [];
        foreach(['single', 'wildcard'] as $type)
        {
            $left = max(0, (int)$purchased[$type] - (int)$current[$type]);
            if($left)
            {
                $remove[$type] = $left;
            }
        }
        if(!$remove)
        {
            return;
        }
        list($ledger, $refund, $known) = self::take(self::ledger($serviceId, $purchased), $remove, self::lastReissue($serviceId));

        self::saveLedger($serviceId, array_filter($ledger, function($entry) { return (int)$entry['qty'] > 0; }));
        self::rememberPurchased($serviceId, $current);
        $text = 'SANs reduced by the client: ' . implode(', ', array_map(function($type, $qty) { return $qty . ' ' . $type; }, array_keys($remove), $remove))
            . ' - reduce them at the vendor too if it refunds extra SANs';
        if($upgrade && $known && \WHMCS\Config\Setting::getValue('CreditOnDowngrade') === 'on')
        {
            $text .= self::creditDifference($serviceId, $upgrade, $refund);
        }
        CertificateService::event($serviceId, 'info', $text);
        GeneralSettings::activity('Service #' . $serviceId . ': ' . $text);
    }

    /** 'single' | 'wildcard': the SAN option an upgrade row changed (null when it is not a SAN option) */
    protected static function upgradeType($serviceId, $upgrade)
    {
        $configId = (int)explode('=>', (string)$upgrade->originalvalue)[0];
        foreach(self::optionQuantities($serviceId) as $type => $row)
        {
            if((int)$row->configid === $configId)
            {
                return $type;
            }
        }

        return null;
    }

    /** Credits the client the difference between the price paid and WHMCS's prorated downgrade credit */
    protected static function creditDifference($serviceId, $upgrade, $refund)
    {
        // this upgrade's own credit (the hook runs before WHMCS links the upgrade to its order)
        $whmcs      = (float)$upgrade->amount < 0 ? -(float)$upgrade->amount : 0.0;
        $difference = round($refund - $whmcs, 2);
        if(abs($difference) < 0.005)
        {
            return '; credited ' . number_format($refund, 2, '.', '') . ' (the price paid)';
        }
        $clientId = (int)Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('userid');
        $balance  = (float)Capsule::table('tblclients')->where('id', $clientId)->value('credit');
        // never below zero: credit the client has already used is not taken back
        $difference = max($difference, -$balance);
        Capsule::table('tblcredit')->insert([
            'clientid'    => $clientId,
            'date'        => date('Y-m-d'),
            'description' => 'Upgrade/Downgrade Credit: additional SANs refunded at the price paid',
            'amount'      => $difference,
            'relid'       => 0,
        ]);
        Capsule::table('tblclients')->where('id', $clientId)->update(['credit' => round($balance + $difference, 2)]);

        return '; credited ' . number_format($refund, 2, '.', '') . ' (the price paid; WHMCS credited ' . number_format($whmcs, 2, '.', '') . ', difference ' . number_format($difference, 2, '.', '') . ')';
    }

    /**
     * Error message when the posted quantities lower the SANs of an ordered certificate more than allowed, '' otherwise:
     * only SANs bought after the order, within DOWNGRADE_DAYS, not used by the certificate and not followed by a reissue
     * (the vendor refunds nothing else)
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
                return 'You can only remove additional domains bought within the last ' . self::DOWNGRADE_DAYS . ' days that the certificate does not use yet and only if it has not been reissued since they were bought (' . $reducible[$type] . ' can be removed now).';
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

    public static function isOrderedCertificate($serviceId)
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
