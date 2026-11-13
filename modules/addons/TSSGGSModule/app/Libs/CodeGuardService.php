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
 * Gateway defects worked around here (reported 2026-11-13, gateway-issues-codeguard-2026-11-13.md):
 * - the details call reports both subscription dates a year ahead and equal to each other, so the period is computed
 *   from the order date and the term instead;
 * - the whole "license" block comes back null, so the figures are shown only when the gateway fills them in;
 * - GET /websites answers 500 while the licence has no website yet, which is treated as an empty list.
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
     * @param array $input optional first_name, last_name, email
     */
    public static function create($serviceId, array $input = [])
    {
        [$service, $ssl] = static::requireOrderable($serviceId);

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

    /** the renewal orders the next licence with the same contact */
    protected static function renewWithSameDetails($serviceId, array $before)
    {
        static::create($serviceId, (array)($before['contact'] ?? []));

        return true;
    }

    // ------------------------------------------------------------------ details

    /**
     * The details call answers with its own shape - status, vendor_id, order_date, subscription and license at the
     * top level - instead of the order / items of a certificate, so both are mapped here.
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

        $license = isset($response['license']) ? array_filter((array)$response['license'], function($value) { return $value !== null && $value !== ''; }) : [];
        $stored['license'] = $license;

        return static::applyItem($stored, (array)($response['items'][0] ?? []));
    }

    /**
     * Period of the licence as the client is shown it. The gateway's own subscription dates cannot be used (both are
     * reported a year ahead and equal to each other), so the period is the order date plus the term that was bought.
     *
     * @return array ['start' => Y-m-d, 'end' => Y-m-d] or [] while nothing is ordered
     */
    public static function period($serviceId)
    {
        $stored = static::stored($serviceId);
        $start  = (string)($stored['orderedAt'] ?? '');
        $term   = (int)($stored['term'] ?? 0);
        if($start === '' || $term <= 0)
        {
            return [];
        }
        $start = date('Y-m-d', strtotime($start));

        return ['start' => $start, 'end' => date('Y-m-d', strtotime('+' . $term . ' months', strtotime($start)))];
    }

    /**
     * Figures the gateway reports for the licence (quotas, usage, backup period, last backup). Empty while the
     * gateway answers them as null.
     */
    public static function licenseInfo($serviceId)
    {
        return (array)(static::stored($serviceId)['license'] ?? []);
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
            throw new \Exception('The gateway did not return a CodeGuard dashboard link');
        }

        return $link;
    }

    /**
     * Websites the licence backs up. The gateway answers 500 while the licence has none (defect reported
     * 2026-11-13), which is indistinguishable from an empty list here, so both give an empty list.
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
            throw new \Exception('The CodeGuard product has no gateway product');
        }

        $response = (array)CertificateService::api($serviceId)->upgradeCodeGuardProduct((int)$stored['itemId'], $productId);
        $orderId  = (int)($response['order']['id'] ?? 0);
        $itemId   = (int)($response['items'][0]['id'] ?? 0);
        if($orderId <= 0 || $itemId <= 0)
        {
            throw new \Exception('The gateway did not return the order of the new plan');
        }

        $previous   = (array)($stored['previous'] ?? []);
        $previous[] = ['orderId' => $stored['orderId'] ?? null, 'itemId' => $stored['itemId'], 'status' => $stored['status'] ?? ''];

        $stored['previous']  = array_slice($previous, -10);
        $stored['orderId']   = $orderId;
        $stored['itemId']    = $itemId;
        $stored['orderedAt'] = date('Y-m-d H:i:s');
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
