<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates;
use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TSSGGSModule\App\Models\Request;
use WHMCS\Database\Capsule;

/**
 * S/MIME (email signing) certificates of GoGetSSL, ordered through the gateway (/certificates/smime).
 *
 * The order is kept in the module's Requests table (name "smime", encrypted JSON):
 * orderId, itemId, status, emails [{email, approved}], vendorOrderId, validFrom, validTill, term, csrProvided,
 * orderedAt, refreshedAt, previous [{orderId, itemId, status, validTill}] (orders before a renewal).
 * tblsslorders: remoteid = item ID, status Awaiting Configuration -> Configuration Submitted -> Completed / Cancelled.
 * tblhosting.domain = the first email address (the certificate subject).
 */
class SmimeService
{
    const CATEGORY     = 'smime';
    const REQUEST_NAME = 'smime';

    // most addresses the order form takes (subject + alternative names); the gateway enforces the product's own limit
    const MAX_EMAILS = 10;

    // the order will not change any more: no automatic refresh
    const FINAL_STATUSES = ['active', 'expired', 'cancelled', 'failed', 'rejected', 'revoked', 'refunded'];

    // contact fields the gateway takes for organization-validated products (same as GoGetSSL API v2)
    const ORGANIZATION_FIELDS  = ['name', 'division', 'duns', 'lei', 'phone', 'country', 'region', 'city', 'postal_code', 'address_line_1', 'address_line_2'];
    const ADMINISTRATOR_FIELDS = ['first_name', 'last_name', 'title', 'organization', 'email', 'phone', 'country', 'region', 'city', 'postal_code', 'address_line_1', 'address_line_2'];
    const ORGANIZATION_REQUIRED  = ['name', 'phone', 'country', 'city', 'address_line_1'];
    const ADMINISTRATOR_REQUIRED = ['first_name', 'last_name', 'email', 'phone', 'country'];

    // days after the order on which the client is reminded to confirm the mailbox
    const REMINDER_DAYS = [2, 7];

    /** @var array WHMCS product ID => bool */
    protected static $products = [];

    public static function isSmimeWhmcsProduct($whmcsProductId)
    {
        $whmcsProductId = (int)$whmcsProductId;
        if(!isset(self::$products[$whmcsProductId]))
        {
            $product = Capsule::table('tblproducts')->where('id', $whmcsProductId)->first(['servertype', 'configoption9']);
            self::$products[$whmcsProductId] = $product && strtolower((string)$product->configoption9) === self::CATEGORY;
        }

        return self::$products[$whmcsProductId];
    }

    public static function isSmimeService($serviceId)
    {
        $packageId = (int)Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('packageid');

        return $packageId && self::isSmimeWhmcsProduct($packageId);
    }

    /**
     * Gateway products the module can sell: GoGetSSL S/MIME (the TheSSLStore S/MIME products take other order data)
     */
    public static function isSupportedRemoteProduct($vendor, $category)
    {
        return strtolower((string)$category) === self::CATEGORY && strtoupper((string)$vendor) === 'GGS';
    }

    /**
     * Gateway product of the WHMCS product (name, validation, prices)
     */
    public static function remoteProduct($whmcsProductId)
    {
        $product = Capsule::table('tblproducts')->where('id', (int)$whmcsProductId)->first(['configoption1', 'configoption2']);
        if(!$product)
        {
            return null;
        }

        return RemoteProduct::where('remoteId', $product->configoption2)->where('vendor', strtoupper((string)$product->configoption1))->first();
    }

    /**
     * OV products (organization + administrator contacts): the gateway product says so, else the product name
     */
    public static function requiresOrganization($whmcsProductId)
    {
        $remote     = self::remoteProduct($whmcsProductId);
        $validation = $remote ? strtoupper((string)$remote->validation) : '';
        if($validation !== '')
        {
            return in_array($validation, ['OV', 'EV'], true);
        }

        $name = $remote ? (string)$remote->name : (string)Capsule::table('tblproducts')->where('id', (int)$whmcsProductId)->value('name');

        return (bool)preg_match('/organi[sz]ation|business|\bOV[A-Z]?\b|sponsor/i', $name);
    }

    /**
     * Terms (months) the gateway sells for the product
     */
    public static function availableTerms($whmcsProductId)
    {
        $remote = self::remoteProduct($whmcsProductId);
        $terms  = [];
        foreach(($remote && is_array($remote->rawData['prices'] ?? null)) ? $remote->rawData['prices'] : [] as $price)
        {
            if(!empty($price['term']))
            {
                $terms[] = (int)$price['term'];
            }
        }

        return array_values(array_unique($terms));
    }

    /**
     * Certificate term for the WHMCS billing cycle: 2 / 3 years for Biennially / Triennially, else 1 year,
     * limited to what the gateway sells
     */
    public static function termForBillingCycle($billingCycle, array $availableTerms)
    {
        $map  = ['Biennially' => 24, 'Triennially' => 36];
        $term = $map[$billingCycle] ?? 12;

        $availableTerms = array_values(array_filter(array_map('intval', $availableTerms)));
        if(!$availableTerms || in_array($term, $availableTerms, true))
        {
            return $term;
        }
        if(in_array(12, $availableTerms, true))
        {
            return 12;
        }
        sort($availableTerms);

        return $availableTerms[0];
    }

    /**
     * Stored order ([] before the order is placed)
     */
    public static function stored($serviceId)
    {
        $row = Request::where('serviceid', (int)$serviceId)->where('name', self::REQUEST_NAME)->first();
        if(!$row || empty($row->request))
        {
            return [];
        }

        $data = json_decode((string)\decrypt($row->request), true);

        return is_array($data) ? $data : [];
    }

    public static function isOrdered($serviceId)
    {
        $stored = self::stored($serviceId);

        return !empty($stored['itemId']);
    }

    protected static function save($serviceId, array $data)
    {
        $values = [
            'request'    => \encrypt(json_encode($data)),
            'status'     => (string)($data['status'] ?? ''),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if(Request::where('serviceid', (int)$serviceId)->where('name', self::REQUEST_NAME)->exists())
        {
            Request::where('serviceid', (int)$serviceId)->where('name', self::REQUEST_NAME)->update($values);
        }
        else
        {
            $request             = new Request();
            $request->serviceid  = (int)$serviceId;
            $request->name       = self::REQUEST_NAME;
            $request->request    = $values['request'];
            $request->status     = $values['status'];
            $request->save();
        }
    }

    /**
     * Places the order at the gateway.
     *
     * @param array $input emails (validated, first = subject), csr (optional), organization / administrator (OV products,
     *                     validated by validateContacts())
     */
    public static function create($serviceId, array $input)
    {
        $service = Capsule::table('tblhosting')->where('id', (int)$serviceId)->first(['id', 'userid', 'packageid', 'billingcycle', 'domainstatus']);
        if(!$service || !self::isSmimeWhmcsProduct($service->packageid))
        {
            throw new \Exception('This service is not an S/MIME certificate');
        }
        if($service->domainstatus !== 'Active')
        {
            throw new \Exception('The service is not active');
        }

        $ssl = CertificateService::sslOrder($serviceId);
        if(!$ssl)
        {
            throw new \Exception('The create action has not been initialized');
        }
        if(self::isOrdered($serviceId) || $ssl->status !== 'Awaiting Configuration')
        {
            throw new \Exception('The S/MIME certificate has already been ordered');
        }

        $productId = (string)Capsule::table('tblproducts')->where('id', (int)$service->packageid)->value('configoption2');
        $term      = self::termForBillingCycle($service->billingcycle, self::availableTerms($service->packageid));
        $emails    = array_values($input['emails']);

        $payload = [
            'product' => ['id' => $productId, 'term' => $term],
            'emails'  => $emails,
        ];
        if(!empty($input['csr']))
        {
            $payload['csr'] = trim((string)$input['csr']);
        }
        if(!empty($input['organization']) || !empty($input['administrator']))
        {
            $payload['contacts'] = [
                'organization'  => $input['organization'],
                'administrator' => $input['administrator'],
            ];
        }

        $response = CertificateService::api($serviceId)->createSmime($payload);
        $orderId  = (int)($response['order']['id'] ?? 0);
        $itemId   = (int)($response['items'][0]['id'] ?? 0);
        if($orderId <= 0 || $itemId <= 0)
        {
            throw new \Exception('The gateway did not return an order ID');
        }

        $stored = self::applyDetails([
            'orderId'     => $orderId,
            'itemId'      => $itemId,
            'term'        => $term,
            'csrProvided' => !empty($payload['csr']),
            'orderedAt'   => date('Y-m-d H:i:s'),
            'emails'      => array_map(function($email) { return ['email' => $email, 'approved' => false]; }, $emails),
            'previous'    => self::stored($serviceId)['previous'] ?? [],
        ], $response);
        self::save($serviceId, $stored);

        Capsule::table('tblsslorders')->where('id', $ssl->id)->update([
            'remoteid'   => $itemId,
            'status'     => ($stored['status'] ?? '') === 'active' ? 'Completed' : 'Configuration Submitted',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        Capsule::table('tblhosting')->where('id', (int)$serviceId)->update(['domain' => $emails[0]]);
        self::saveCost($serviceId, (float)($response['order']['total_amount'] ?? 0));

        CertificateService::event($serviceId, 'info', 'S/MIME certificate ordered for ' . implode(', ', $emails) . ' (order #' . $orderId . ')', (int)$service->userid);
        if(($stored['status'] ?? '') === 'active')
        {
            self::sendIssuedEmail($serviceId, $stored);
        }

        return $stored;
    }

    /**
     * Reads the order from the gateway and saves it
     */
    public static function refresh($serviceId)
    {
        $stored = self::stored($serviceId);
        if(empty($stored['itemId']))
        {
            throw new \Exception('The S/MIME certificate has not been ordered yet');
        }

        $previousStatus = (string)($stored['status'] ?? '');
        $response       = CertificateService::api($serviceId)->getCertificateDetails(self::CATEGORY, (int)$stored['itemId']);
        $stored         = self::applyDetails($stored, $response);
        $stored['refreshedAt'] = date('Y-m-d H:i:s');
        self::save($serviceId, $stored);

        // the cron takes the orders refreshed longest ago first
        Capsule::table('tblsslorders')->where('serviceid', (int)$serviceId)->update(['updated_at' => date('Y-m-d H:i:s')]);

        $status = (string)($stored['status'] ?? '');
        if($status !== '' && $status !== $previousStatus)
        {
            $update = [];
            if($status === 'active')
            {
                $update['status']         = 'Completed';
                $update['completiondate'] = date('Y-m-d H:i:s');

                // the certificate is valid from its issue date: the next due date follows its end (same rule and
                // due date offset setting as the SSL certificates)
                $dueDate = SslProductHelpers::getServiceDueDate((array)$response, []);
                if($dueDate)
                {
                    Capsule::table('tblhosting')->where('id', (int)$serviceId)->update(['nextduedate' => $dueDate, 'nextinvoicedate' => $dueDate]);
                }
            }
            elseif(in_array($status, ['cancelled', 'revoked', 'refunded'], true))
            {
                $update['status'] = 'Cancelled';
            }
            if($update)
            {
                Capsule::table('tblsslorders')->where('serviceid', (int)$serviceId)->update($update);
            }

            CertificateService::event($serviceId, in_array($status, ['failed', 'rejected'], true) ? 'error' : 'info', 'S/MIME certificate status: ' . ($previousStatus ?: '-') . ' -> ' . $status);
            if($status === 'active')
            {
                self::sendIssuedEmail($serviceId, $stored);
            }
        }

        return $stored;
    }

    /**
     * The client page reads the order from the gateway when it was last read more than $seconds ago
     */
    public static function refreshDue(array $stored, $seconds = 60)
    {
        if(empty($stored['itemId']) || in_array($stored['status'] ?? '', self::FINAL_STATUSES, true))
        {
            return false;
        }

        return empty($stored['refreshedAt']) || strtotime($stored['refreshedAt']) < time() - (int)$seconds;
    }

    /**
     * "S/MIME Certificate Issued" e-mail: how the client gets the certificate
     */
    protected static function sendIssuedEmail($serviceId, array $stored)
    {
        try
        {
            EmailTemplates::sendEmail(EmailTemplates::SMIME_ISSUED_TEMPLATE, $serviceId, [
                'smime_emails'       => implode(', ', array_column((array)($stored['emails'] ?? []), 'email')),
                'smime_valid_till'   => !empty($stored['validTill']) ? date('F j, Y', strtotime($stored['validTill'])) : '',
                'smime_csr_provided' => !empty($stored['csrProvided']) ? 1 : 0,
            ]);
        }
        catch(\Throwable $e)
        {
            CertificateService::event($serviceId, 'error', 'S/MIME issued e-mail not sent: ' . $e->getMessage());
        }
    }

    /**
     * "S/MIME Mailbox Not Confirmed" e-mail on day 2 and day 7 after the order while an address is not confirmed
     * (one e-mail per run; the days already passed are marked as sent)
     */
    public static function remindIfUnconfirmed($serviceId)
    {
        $stored = self::stored($serviceId);
        if(!in_array($stored['status'] ?? '', ['pending', 'processing'], true) || empty($stored['orderedAt']))
        {
            return false;
        }
        $pending = [];
        foreach((array)($stored['emails'] ?? []) as $entry)
        {
            if(empty($entry['approved']))
            {
                $pending[] = $entry['email'];
            }
        }
        $days = (int)floor((time() - strtotime($stored['orderedAt'])) / 86400);
        $sent = (array)($stored['remindersSent'] ?? []);
        $due  = array_values(array_filter(self::REMINDER_DAYS, function($day) use ($days, $sent) { return $days >= $day && !in_array($day, $sent, true); }));
        if(!$pending || !$due)
        {
            return false;
        }

        EmailTemplates::sendEmail(EmailTemplates::SMIME_REMINDER_TEMPLATE, $serviceId, [
            'smime_pending_emails' => implode(', ', $pending),
            'smime_ordered_date'   => date('F j, Y', strtotime($stored['orderedAt'])),
        ]);
        $stored['remindersSent'] = array_values(array_unique(array_merge($sent, $due)));
        self::save($serviceId, $stored);
        CertificateService::event($serviceId, 'info', 'S/MIME reminder sent: mailbox not confirmed (' . implode(', ', $pending) . ')');

        return true;
    }

    /**
     * Gateway cost, WHMCS price and invoice of the order on the request row (Reports: cost and profit)
     */
    protected static function saveCost($serviceId, $apiPrice)
    {
        $service   = Capsule::table('tblhosting')->where('id', (int)$serviceId)->first(['amount']);
        $invoiceId = (int)Capsule::table('tblinvoiceitems')->where('relid', (int)$serviceId)->where('type', 'Hosting')->orderBy('invoiceid', 'desc')->value('invoiceid');
        $rate      = (float)((new \ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository())->getModuleConfiguration()['financeSettings']['rate'] ?? 1) ?: 1;
        $amount    = $service ? (float)$service->amount : 0;

        Request::where('serviceid', (int)$serviceId)->where('name', self::REQUEST_NAME)->update([
            'invoiceid'   => $invoiceId,
            'api_price'   => $apiPrice,
            'whmcs_price' => $amount,
            'rate'        => $rate,
            'diff_price'  => $amount - $apiPrice,
        ]);
    }

    /**
     * Refreshes the open orders (certificates cron). Returns [serviceId => error] of the orders that failed.
     */
    public static function refreshOpen($limit = 100)
    {
        $serviceIds = Capsule::table('tblhosting')
            ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->join('tblsslorders', 'tblsslorders.serviceid', '=', 'tblhosting.id')
            ->where('tblproducts.servertype', 'TSSGGSModule')
            ->where('tblproducts.configoption9', self::CATEGORY)
            ->where('tblhosting.domainstatus', 'Active')
            ->where('tblsslorders.status', 'Configuration Submitted')
            ->orderBy('tblsslorders.updated_at', 'asc')
            ->limit((int)$limit)
            ->pluck('tblhosting.id');

        $failed = [];
        foreach($serviceIds as $serviceId)
        {
            try
            {
                $stored = self::stored($serviceId);
                if(!empty($stored['itemId']) && !in_array($stored['status'] ?? '', self::FINAL_STATUSES, true))
                {
                    self::refresh($serviceId);
                    self::remindIfUnconfirmed($serviceId);
                }
            }
            catch(\Throwable $e)
            {
                $failed[(int)$serviceId] = $e->getMessage();
            }
        }

        return $failed;
    }

    /**
     * Cancels the order at the gateway (Terminate, admin "Cancel Order"). False when there is nothing to cancel.
     */
    public static function cancel($serviceId, $reason)
    {
        $stored = self::stored($serviceId);
        // an issued (active) certificate can still be cancelled; the other final states can not
        if(empty($stored['itemId']) || in_array($stored['status'] ?? '', array_diff(self::FINAL_STATUSES, ['active']), true))
        {
            return false;
        }

        CertificateService::api($serviceId)->cancelItem((int)$stored['itemId'], $reason, self::CATEGORY);

        $stored['status'] = 'cancelled';
        self::save($serviceId, $stored);
        Capsule::table('tblsslorders')->where('serviceid', (int)$serviceId)->update(['status' => 'Cancelled', 'updated_at' => date('Y-m-d H:i:s')]);

        return true;
    }

    /**
     * Renewal: an S/MIME certificate is not renewed in place - the service goes back to Awaiting Configuration and the
     * client orders the next certificate (the current one stays valid until it expires).
     *
     * @return bool true when the service was reset (false: nothing ordered yet)
     */
    public static function resetForRenewal($serviceId)
    {
        $stored = self::stored($serviceId);
        if(empty($stored['itemId']))
        {
            return false;
        }

        $previous   = (array)($stored['previous'] ?? []);
        $previous[] = [
            'orderId'   => $stored['orderId'] ?? null,
            'itemId'    => $stored['itemId'],
            'status'    => $stored['status'] ?? '',
            'validTill' => $stored['validTill'] ?? '',
            'emails'    => array_column((array)($stored['emails'] ?? []), 'email'),
        ];
        self::save($serviceId, ['status' => 'renewal', 'previous' => array_slice($previous, -10)]);

        Capsule::table('tblsslorders')->where('serviceid', (int)$serviceId)->update([
            'remoteid'   => '',
            'status'     => 'Awaiting Configuration',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        CertificateService::event($serviceId, 'info', 'S/MIME renewal: the service waits for the next certificate order (previous order #' . ($stored['orderId'] ?? '-') . ')');

        return true;
    }

    /**
     * Status, emails, CA order ID and validity from a create / details response
     */
    protected static function applyDetails(array $stored, $response)
    {
        $response = (array)$response;
        $item     = isset($response['items'][0]) ? (array)$response['items'][0] : [];

        if(!empty($response['order']['status']))
        {
            $stored['status'] = strtolower((string)$response['order']['status']);
        }
        if(!empty($response['order']['id']))
        {
            $stored['orderId'] = (int)$response['order']['id'];
        }
        if(isset($item['emails']) && is_array($item['emails']) && $item['emails'])
        {
            $stored['emails'] = self::normalizeEmails($item['emails']);
        }
        if(!empty($item['vendor']['id']))
        {
            $stored['vendorOrderId'] = (string)$item['vendor']['id'];
        }
        $validity = isset($item['subscription']) ? (array)$item['subscription'] : (isset($response['validity']) ? (array)$response['validity'] : []);
        if(!empty($validity['begin']))
        {
            $stored['validFrom'] = (string)$validity['begin'];
        }
        if(!empty($validity['end']))
        {
            $stored['validTill'] = (string)$validity['end'];
        }

        return $stored;
    }

    /**
     * [['email' => .., 'approved' => bool], ...] from the gateway's list (strings or objects)
     */
    public static function normalizeEmails($emails)
    {
        $result = [];
        foreach((array)$emails as $entry)
        {
            if(is_string($entry))
            {
                $result[] = ['email' => $entry, 'approved' => false];
                continue;
            }
            $entry = (array)$entry;
            $email = $entry['email'] ?? ($entry['name'] ?? '');
            if($email !== '')
            {
                $result[] = ['email' => (string)$email, 'approved' => !empty($entry['approved'])];
            }
        }

        return $result;
    }

    /**
     * Unique, lower-case addresses from the form (first = subject). Invalid ones are returned in $invalid.
     */
    public static function parseEmails(array $values, &$invalid = [])
    {
        $result  = [];
        $invalid = [];
        foreach($values as $value)
        {
            foreach(preg_split('/[\s,;]+/', (string)$value, -1, PREG_SPLIT_NO_EMPTY) as $email)
            {
                $email = strtolower(trim($email));
                if(filter_var($email, FILTER_VALIDATE_EMAIL) === false)
                {
                    $invalid[] = $email;
                }
                elseif(!in_array($email, $result, true))
                {
                    $result[] = $email;
                }
            }
        }

        return $result;
    }

    /**
     * Organization and administrator contacts with every field the gateway takes (empty = null), country codes
     * upper-case. Returns the first missing required field as [group, field] in $missing.
     */
    public static function contacts(array $organization, array $administrator, &$missing = null)
    {
        $missing = null;
        $org     = [];
        foreach(self::ORGANIZATION_FIELDS as $field)
        {
            $value       = trim((string)($organization[$field] ?? ''));
            $org[$field] = $value !== '' ? $value : null;
        }
        $admin = [];
        foreach(self::ADMINISTRATOR_FIELDS as $field)
        {
            $value         = trim((string)($administrator[$field] ?? ''));
            $admin[$field] = $value !== '' ? $value : null;
        }
        if($admin['organization'] === null)
        {
            $admin['organization'] = $org['name'];
        }

        foreach(self::ORGANIZATION_REQUIRED as $field)
        {
            if($org[$field] === null)
            {
                $missing = ['org', $field];
                break;
            }
        }
        if(!$missing)
        {
            foreach(self::ADMINISTRATOR_REQUIRED as $field)
            {
                if($admin[$field] === null)
                {
                    $missing = ['admin', $field];
                    break;
                }
            }
        }

        $org['country']   = $org['country'] !== null ? strtoupper($org['country']) : null;
        $admin['country'] = $admin['country'] !== null ? strtoupper($admin['country']) : null;

        return ['organization' => $org, 'administrator' => $admin];
    }
}
