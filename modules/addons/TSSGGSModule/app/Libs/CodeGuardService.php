<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use WHMCS\Database\Capsule;

/**
 * CodeGuard website backup of TheSSLStore, ordered through the gateway (/code-guard).
 *
 * Unlike the certificates there is nothing for the client to fill in: the licence is created the moment the service
 * is set up, from the client's own name and e-mail address, and everything after that happens in the CodeGuard
 * dashboard, which the client opens through a one-time SSO link. The licence can be suspended and activated again
 * (WHMCS suspend / unsuspend), moved to another plan (ChangePackage) and deleted (Terminate).
 *
 * Stored order (see GatewayOrderService) plus: contact {first_name, last_name, email}, vendor licence id and the
 * licence figures the gateway reports (quotas, backup period, last backup).
 * tblhosting.domain = the administrator's e-mail address (the licence has no domain of its own; the websites are
 * added by the client inside the dashboard).
 *
 * The gateway answers like for a certificate since 2026-11-19: {order, items[0]} with items[0].subscription
 * {begin, next_renewal while active | end once cancelled} and items[0].license (quotas, contact). Answers in the
 * earlier flat shape (status, vendor_id, order_date, license at the top) are still read.
 *
 * Gateway / vendor issues worked around here (gateway-issues-codeguard-2026-11-13.md):
 * - until the gateway dates are known (or for the flat shape, whose dates were wrong) the period is the order date
 *   plus the term;
 * - an empty or null "license" block does not erase what an earlier refresh found.
 */
class CodeGuardService extends GatewayOrderService
{
    const CATEGORY     = SSLTrustCenterApi::CODE_GUARD_CATEGORY;
    const REQUEST_NAME = 'codeguard';
    const LABEL        = 'CodeGuard';

    /** licence states the gateway reports */
    const STATUS_ACTIVE    = 'active';
    const STATUS_SUSPENDED = 'suspended';

    /** nothing has to be configured: the e-mail only points the client at the dashboard */
    public static function configurationTemplate()
    {
        return \ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates::CODE_GUARD_READY_TEMPLATE;
    }

    /**
     * Gateway products the module can sell: the CodeGuard products of TheSSLStore. GoGetSSL does not resell them -
     * the gateway answers "Unsupported action" for a GoGetSSL reseller.
     */
    public static function isSupportedRemoteProduct($vendor, $category)
    {
        return strtolower((string)$category) === self::CATEGORY && strtoupper((string)$vendor) === 'TSS';
    }

    public static function isCodeGuardWhmcsProduct($whmcsProductId)
    {
        return static::isWhmcsProduct($whmcsProductId);
    }

    public static function isCodeGuardService($serviceId)
    {
        return static::isService($serviceId);
    }

    // ------------------------------------------------------------------ order

    /**
     * Places the licence order. The contact defaults to the client's own name and e-mail address; the admin and the
     * client can change it afterwards (editContact).
     *
     * @param array $input       optional first_name, last_name, email
     * @param bool  $duringSetup called from the module's CreateAccount, where WHMCS has not set the service to Active yet
     */
    public static function create($serviceId, array $input = [], $duringSetup = false)
    {
        [$service, $ssl] = $duringSetup ? static::requireSetupOrderable($serviceId) : static::requireOrderable($serviceId);

        $productId = (string)Capsule::table('tblproducts')->where('id', (int)$service->packageid)->value('configoption2');
        $term      = static::termForBillingCycle($service->billingcycle, static::availableTerms($service->packageid));
        $contact   = static::contact($serviceId, $input);

        $payload = [
            'product'  => ['id' => SSLTrustCenterApi::productId($productId), 'term' => $term],
            'contacts' => ['administrator' => $contact],
        ];

        $response = CertificateService::api($serviceId)->createCodeGuard($payload);

        return static::saveCreated($serviceId, $ssl, [
            'request' => $payload,
            'term'    => $term,
            'contact' => $contact,
        ], $response, $contact['email'], 'CodeGuard licence ordered for ' . $contact['email']);
    }

    /**
     * The checks of requireOrderable() without the "service is active" one: WHMCS sets the service to Active only
     * after the module's CreateAccount has returned, so during provisioning it is still Pending.
     *
     * @return array [service row, tblsslorders row]
     */
    protected static function requireSetupOrderable($serviceId)
    {
        $service = Capsule::table('tblhosting')->where('id', (int)$serviceId)->first(['id', 'userid', 'packageid', 'billingcycle', 'domainstatus', 'amount']);
        if(!$service || !static::isWhmcsProduct($service->packageid))
        {
            throw new \Exception('This service is not a ' . static::LABEL . ' licence');
        }

        $ssl = CertificateService::sslOrder($serviceId);
        if(!$ssl)
        {
            throw new \Exception('The create action has not been initialized');
        }
        if(static::isOrdered($serviceId) || $ssl->status !== 'Awaiting Configuration')
        {
            throw new \Exception('The ' . static::LABEL . ' licence has already been ordered');
        }

        return [$service, $ssl];
    }

    /**
     * Administrator contact of the licence: what was passed, else the client's own details. The gateway takes the
     * first name, the last name and the e-mail address, nothing else.
     */
    public static function contact($serviceId, array $input = [])
    {
        $client = Capsule::table('tblhosting')
            ->join('tblclients', 'tblclients.id', '=', 'tblhosting.userid')
            ->where('tblhosting.id', (int)$serviceId)
            ->first(['tblclients.firstname', 'tblclients.lastname', 'tblclients.email']);

        $contact = [
            'first_name' => trim((string)($input['first_name'] ?? ($client->firstname ?? ''))),
            'last_name'  => trim((string)($input['last_name'] ?? ($client->lastname ?? ''))),
            'email'      => trim((string)($input['email'] ?? ($client->email ?? ''))),
        ];
        foreach($contact as $field => $value)
        {
            if($value === '')
            {
                throw new \Exception('The CodeGuard licence needs the client\'s ' . str_replace('_', ' ', $field));
            }
        }
        if(!filter_var($contact['email'], FILTER_VALIDATE_EMAIL))
        {
            throw new \Exception('The e-mail address of the CodeGuard licence is not valid');
        }

        return $contact;
    }

    /**
     * A paid renewal does NOT order a second licence: the licence runs on at the vendor, so only the period the
     * client is shown moves on by the term that was paid for. (The gateway has no renewal call for CodeGuard; if one
     * is added, it belongs here.)
     *
     * @return string 'kept' (nothing to order) or '' (nothing ordered yet)
     */
    public static function renewal($serviceId)
    {
        $stored = static::stored($serviceId);
        if(empty($stored['itemId']))
        {
            return '';
        }

        $period = static::period($serviceId);
        $from   = ($period && $period['end'] > date('Y-m-d')) ? $period['end'] : date('Y-m-d');
        $term   = (int)($stored['term'] ?? 12) ?: 12;

        $stored['periodEnd'] = date('Y-m-d', strtotime('+' . $term . ' months', strtotime($from)));
        static::save($serviceId, $stored);
        CertificateService::event($serviceId, 'info', 'CodeGuard renewal paid: the licence continues until ' . $stored['periodEnd']);

        return 'kept';
    }

    // ------------------------------------------------------------------ details

    /**
     * Details answer: {order, items[0]} (since 2026-11-19, applyItem reads the licence and the dates), or the earlier
     * flat shape - status, vendor_id, order_date and license at the top level.
     */
    protected static function applyDetails(array $stored, $response)
    {
        $response = (array)$response;
        if(isset($response['order']) || isset($response['items']))
        {
            return parent::applyDetails($stored, $response);
        }

        if(!empty($response['status']))
        {
            $stored['status'] = strtolower((string)$response['status']);
        }
        if(!empty($response['vendor_id']))
        {
            $stored['vendorOrderId'] = (string)$response['vendor_id'];
        }
        if(!empty($response['order_date']))
        {
            $stored['orderedAt'] = date('Y-m-d H:i:s', strtotime((string)$response['order_date']));
        }

        // the gateway reports the whole block as null (defect 2026-11-13): an empty answer must not erase what an
        // earlier refresh found
        $license = isset($response['license']) ? array_filter((array)$response['license'], function($value) { return $value !== null && $value !== ''; }) : [];
        if($license)
        {
            $stored['license'] = $license;
        }

        return static::applyItem($stored, (array)($response['items'][0] ?? []));
    }

    /**
     * items[0] of the answer: the licence figures and the subscription dates (begin; next_renewal while the licence
     * runs, end once it is cancelled). They are kept as subscriptionBegin / subscriptionEnd for period(); validFrom /
     * validTill (certificate dates) are not used for a licence.
     */
    protected static function applyItem(array $stored, array $item)
    {
        unset($stored['validFrom'], $stored['validTill']);

        // an empty answer must not erase what an earlier refresh found
        $license = isset($item['license']) ? array_filter((array)$item['license'], function($value) { return $value !== null && $value !== ''; }) : [];
        if($license)
        {
            $stored['license'] = $license;
        }

        $subscription = isset($item['subscription']) ? (array)$item['subscription'] : [];
        $begin        = (string)($subscription['begin'] ?? '');
        $end          = (string)($subscription['next_renewal'] ?? ($subscription['end'] ?? ''));
        if($begin !== '' && strtotime($begin) && $end !== '' && strtotime($end) > strtotime($begin))
        {
            $stored['subscriptionBegin'] = date('Y-m-d', strtotime($begin));
            $stored['subscriptionEnd']   = date('Y-m-d', strtotime($end));
        }

        return $stored;
    }

    /**
     * A licence changes while it runs (websites, storage, last backup), unlike a certificate: the client page reads it
     * again from time to time even once it is active.
     */
    public static function refreshDue(array $stored, $seconds = 60)
    {
        if(empty($stored['itemId']))
        {
            return false;
        }
        if(($stored['status'] ?? '') === self::STATUS_ACTIVE)
        {
            $seconds = max((int)$seconds, 900);
        }
        elseif(in_array($stored['status'] ?? '', static::FINAL_STATUSES, true))
        {
            return false;
        }

        return empty($stored['refreshedAt']) || strtotime($stored['refreshedAt']) < time() - (int)$seconds;
    }

    /**
     * Period of the licence as the client is shown it: the gateway's subscription dates (begin - next renewal), else
     * the order date plus the term that was bought. A paid renewal (renewal()) moves the end on before the vendor
     * does: the later of the two ends is shown.
     *
     * @return array ['start' => Y-m-d, 'end' => Y-m-d] or [] while nothing is ordered
     */
    public static function period($serviceId)
    {
        $stored = static::stored($serviceId);
        if(!empty($stored['subscriptionBegin']) && !empty($stored['subscriptionEnd']))
        {
            $start = (string)$stored['subscriptionBegin'];
            $end   = (string)$stored['subscriptionEnd'];
        }
        else
        {
            $ordered = (string)($stored['orderedAt'] ?? '');
            $term    = (int)($stored['term'] ?? 0);
            if($ordered === '' || $term <= 0)
            {
                return [];
            }
            $start = date('Y-m-d', strtotime($ordered));
            $end   = date('Y-m-d', strtotime('+' . $term . ' months', strtotime($start)));
        }
        if(!empty($stored['periodEnd']) && (string)$stored['periodEnd'] > $end)
        {
            $end = (string)$stored['periodEnd'];
        }

        return ['start' => $start, 'end' => $end];
    }

    /**
     * Figures the gateway reports for the licence (quotas, usage, backup period, last backup). Empty while the
     * gateway answers them as null.
     */
    public static function licenseInfo($serviceId)
    {
        return (array)(static::stored($serviceId)['license'] ?? []);
    }

    /** licence figures shown, in this order (the contact, time zone and locale of the block are not) */
    const LICENSE_FIGURES = ['website_count_used', 'website_count_limit', 'size_used', 'size_limit', 'backup_period', 'last_backup_date', 'past_due', 'over_quota'];

    /**
     * The licence figures readable: storage in KB / MB / GB, the backup period in days, the last backup as a date;
     * "past due" and "over quota" only when they are true.
     *
     * @param string $daysText ':days: days' in the reader's language
     * @return array key => string (true for past_due / over_quota)
     */
    public static function licenseFigures(array $license, $daysText = ':days: days')
    {
        $figures = [];
        foreach(self::LICENSE_FIGURES as $key)
        {
            if(!array_key_exists($key, $license) || $license[$key] === null || $license[$key] === '')
            {
                continue;
            }
            $value = $license[$key];
            if($key === 'past_due' || $key === 'over_quota')
            {
                if($value === true || $value === 1 || $value === '1' || $value === 'true')
                {
                    $figures[$key] = true;
                }
                continue;
            }
            if($key === 'size_used' || $key === 'size_limit')
            {
                $figures[$key] = self::bytes((float)$value);
            }
            elseif($key === 'backup_period')
            {
                $figures[$key] = str_replace(':days:', (string)(int)round((float)$value / 86400), $daysText);
            }
            elseif($key === 'last_backup_date')
            {
                $figures[$key] = strtotime((string)$value) ? date('Y-m-d H:i', strtotime((string)$value)) : (string)$value;
            }
            else
            {
                $figures[$key] = (string)(int)$value;
            }
        }

        return $figures;
    }

    private static function bytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unit  = 0;
        while($bytes >= 1024 && $unit < count($units) - 1)
        {
            $bytes /= 1024;
            $unit++;
        }

        return rtrim(rtrim(number_format($bytes, 1, '.', ''), '0'), '.') . ' ' . $units[$unit];
    }

    // ------------------------------------------------------------------ client actions

    /**
     * One-time link into the CodeGuard dashboard. It is never stored: each click asks the gateway for a fresh one.
     */
    public static function ssoLink($serviceId)
    {
        $stored = static::requireLicense($serviceId);
        $answer = (array)CertificateService::api($serviceId)->getCodeGuardSsoLink((int)$stored['itemId']);
        $link   = (string)($answer['sso_link'] ?? '');
        if($link === '')
        {
            throw new \Exception('%%VENDOR%% did not return a CodeGuard dashboard link');
        }

        return $link;
    }

    /**
     * Websites the licence backs up ([] when none). An error gives an empty list too (gateways before the CG-1 fix
     * answered 500 for a licence without websites).
     */
    public static function websites($serviceId)
    {
        $stored = static::stored($serviceId);
        if(empty($stored['itemId']))
        {
            return [];
        }

        try
        {
            $answer = CertificateService::api($serviceId)->getCodeGuardWebsites((int)$stored['itemId']);
        }
        catch(\Throwable $e)
        {
            CertificateService::event($serviceId, 'info', 'CodeGuard websites could not be read: ' . $e->getMessage());

            return [];
        }

        return is_array($answer) ? $answer : [];
    }

    /**
     * Changes the administrator contact of the licence.
     */
    public static function editContact($serviceId, array $input)
    {
        $stored  = static::requireLicense($serviceId);
        $contact = static::contact($serviceId, $input);

        CertificateService::api($serviceId)->editCodeGuard((int)$stored['itemId'], ['contacts' => ['administrator' => $contact]]);

        $stored['contact'] = $contact;
        static::save($serviceId, $stored);
        Capsule::table('tblhosting')->where('id', (int)$serviceId)->update(['domain' => mb_substr($contact['email'], 0, 255)]);
        CertificateService::event($serviceId, 'info', 'CodeGuard contact changed to ' . $contact['email']);

        return $contact;
    }

    // ------------------------------------------------------------------ lifecycle

    /** WHMCS suspend: the licence stops backing up, the data is kept */
    public static function suspend($serviceId)
    {
        return static::setSuspended($serviceId, true);
    }

    /** WHMCS unsuspend */
    public static function activate($serviceId)
    {
        return static::setSuspended($serviceId, false);
    }

    protected static function setSuspended($serviceId, $suspended)
    {
        $stored = static::stored($serviceId);
        if(empty($stored['itemId']) || in_array($stored['status'] ?? '', ['cancelled', 'refunded'], true))
        {
            return false;
        }

        $api = CertificateService::api($serviceId);
        $suspended ? $api->suspendCodeGuard((int)$stored['itemId']) : $api->activateCodeGuard((int)$stored['itemId']);

        $stored['status'] = $suspended ? self::STATUS_SUSPENDED : self::STATUS_ACTIVE;
        static::save($serviceId, $stored);
        CertificateService::event($serviceId, 'info', 'CodeGuard licence ' . ($suspended ? 'suspended' : 'activated again'));

        return true;
    }

    /**
     * Moves the licence to another plan (ChangePackage). The gateway places a new order for the new plan and carries
     * the vendor licence over, so the service points at the new order afterwards and the superseded one is gone.
     *
     * @param int $whmcsProductId the product the service is moving to
     */
    /**
     * Upgrade paths between the module's CodeGuard products (WHMCS product > Upgrades), set when products are
     * imported: each plan can be upgraded to every dearer one. Only missing paths are added - paths the admin set
     * (or removed and set differently) are left alone; downgrades are the admin's choice. Returns paths added.
     */
    public static function linkUpgradePaths()
    {
        if(!Capsule::schema()->hasTable('tblproduct_upgrade_products'))
        {
            return 0;
        }
        $plans = [];
        $query = Capsule::table('tblproducts')->whereRaw('LOWER(configoption9) = ?', [strtolower(static::CATEGORY)]);
        if(Capsule::schema()->hasColumn('tblproducts', 'retired'))
        {
            $query->where('retired', 0);
        }
        foreach($query->get(['id']) as $product)
        {
            $remote = static::remoteProduct((int)$product->id);
            $price  = static::comparablePrice($remote && is_array($remote->rawData) ? $remote->rawData : []);
            if($price !== null)
            {
                $plans[(int)$product->id] = $price;
            }
        }
        asort($plans);
        $ids   = array_keys($plans);
        $added = 0;
        for($i = 0; $i < count($ids); $i++)
        {
            for($j = $i + 1; $j < count($ids); $j++)
            {
                if($plans[$ids[$j]] <= $plans[$ids[$i]])
                {
                    continue; // same price: neither is an upgrade of the other
                }
                $exists = Capsule::table('tblproduct_upgrade_products')->where('product_id', $ids[$i])->where('upgrade_product_id', $ids[$j])->exists();
                if(!$exists)
                {
                    Capsule::table('tblproduct_upgrade_products')->insert(['product_id' => $ids[$i], 'upgrade_product_id' => $ids[$j]]);
                    $added++;
                }
            }
        }

        return $added;
    }

    /** Provider price of the shortest term (the same term for every plan, so the plans compare) */
    protected static function comparablePrice(array $raw)
    {
        $best = null;
        foreach((array)($raw['prices'] ?? []) as $price)
        {
            $term    = (int)($price['term'] ?? 0);
            $selling = $price['selling'] ?? ($price['base']['single']['selling'] ?? null);
            if($term > 0 && is_numeric($selling) && ($best === null || $term < $best[0]))
            {
                $best = [$term, (float)$selling / $term];
            }
        }

        return $best === null ? null : $best[1];
    }

    public static function changePlan($serviceId, $whmcsProductId)
    {
        $stored = static::requireLicense($serviceId);
        if(!static::isWhmcsProduct($whmcsProductId))
        {
            throw new \Exception('The service can only be moved to another CodeGuard product');
        }
        $productId = (string)Capsule::table('tblproducts')->where('id', (int)$whmcsProductId)->value('configoption2');
        if($productId === '')
        {
            throw new \Exception('The CodeGuard product has no %%VENDOR%% product');
        }

        $response = (array)CertificateService::api($serviceId)->upgradeCodeGuardProduct((int)$stored['itemId'], $productId);
        $orderId  = (int)($response['order']['id'] ?? 0);
        $itemId   = (int)($response['items'][0]['id'] ?? 0);
        if($orderId <= 0 || $itemId <= 0)
        {
            throw new \Exception('%%VENDOR%% did not return the order of the new plan');
        }

        $previous   = (array)($stored['previous'] ?? []);
        $previous[] = ['orderId' => $stored['orderId'] ?? null, 'itemId' => $stored['itemId'], 'status' => $stored['status'] ?? ''];

        // the licence carries on: the period keeps running from the original order, the plan change does not restart it
        $stored['previous'] = array_slice($previous, -10);
        $stored['orderId']  = $orderId;
        $stored['itemId']   = $itemId;
        $stored = static::applyDetails($stored, $response);
        static::save($serviceId, $stored);

        Capsule::table('tblsslorders')->where('serviceid', (int)$serviceId)->update(['remoteid' => $itemId, 'updated_at' => date('Y-m-d H:i:s')]);
        static::saveCost($serviceId, (float)($response['order']['total_amount'] ?? 0));
        CertificateService::event($serviceId, 'info', 'CodeGuard plan changed (new order #' . $orderId . ')');

        return $stored;
    }

    /**
     * Terminate / admin cancellation: the licence is deleted at the vendor. The documented cancel-subscription call
     * does not exist, so DELETE is used (the order then reports "cancelled").
     */
    public static function cancel($serviceId, $reason)
    {
        $stored = static::stored($serviceId);
        if(empty($stored['itemId']) || in_array($stored['status'] ?? '', array_diff(static::FINAL_STATUSES, ['active']), true))
        {
            return false;
        }

        CertificateService::api($serviceId)->deleteCodeGuard((int)$stored['itemId']);

        $stored['status'] = 'cancelled';
        static::save($serviceId, $stored);
        Capsule::table('tblsslorders')->where('serviceid', (int)$serviceId)->update(['status' => 'Cancelled', 'updated_at' => date('Y-m-d H:i:s')]);
        CertificateService::event($serviceId, 'info', 'CodeGuard licence deleted' . ($reason ? ' (' . $reason . ')' : ''));

        return true;
    }

    /**
     * @return array the stored order, with a licence
     */
    protected static function requireLicense($serviceId)
    {
        $stored = static::stored($serviceId);
        if(empty($stored['itemId']))
        {
            throw new \Exception('The CodeGuard licence has not been ordered yet');
        }

        return $stored;
    }
}
