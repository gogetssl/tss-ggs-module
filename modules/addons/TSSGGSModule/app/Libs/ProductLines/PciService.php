<?php

namespace TSSGGSModule\App\Libs\ProductLines;

use TSSGGSModule\App\Helpers\EmailTemplates;
use WHMCS\Database\Capsule;
use TSSGGSModule\App\Libs\Certificates\AddonCertificates;
use TSSGGSModule\App\Libs\Certificates\CertificateService;
use TSSGGSModule\App\Libs\Gateway\SSLTrustCenterApi;
use TSSGGSModule\App\Libs\Products\SslProductHelpers;

/**
 * HackerGuardian PCI scanning of TheSSLStore (Sectigo, run on Qualys), ordered through the gateway (/pci).
 *
 * Full order: the client enters the organization and the administrator after paying, POST /pci creates the scan
 * account at once (status active) and its answer carries the login of the scan control centre
 * (items[0].login {username, password}). TheSSLStore hands the login out only in that answer - GET /pci/{id} does not
 * return it - so the module keeps it (encrypted with the rest of the order) and shows it to the client and the admin.
 * The IP addresses to scan are entered by the client in the scan control centre, not through the gateway.
 *
 * GET /pci/{order id} gives the status and the scanning period (items[0].subscription {begin, end}); the order ID and
 * the item ID are the same. Cancel / refund: POST /orders/{order id}/cancel.
 *
 * Vendor rule: the organization name is unique at the scanning service. A second order for the same name is refused
 * (TSS-9009 "the company name already exists in the service qualys") and the name is not freed by a refund. How a
 * renewal is ordered is an open question to TheSSLStore (2026-11-20), so a paid renewal keeps the account and asks
 * the admin to renew it by hand - see renewal().
 *
 * Products: hgpcicontrolscan (Scan Control Center, 5 IPs), comodopciscan (Enterprise Edition, 20 IPs); TheSSLStore only.
 *
 * Stored order (see GatewayOrderService) plus: organization (name), contacts {organization, administrator},
 * login {username, password}. tblhosting.domain = the organization name.
 */
class PciService extends GatewayOrderService
{
    const CATEGORY     = 'pci';
    const REQUEST_NAME = 'pci';
    const LABEL        = 'HackerGuardian PCI';

    /** gateway products the module sells, per vendor */
    const PRODUCTS = ['TSS' => ['hgpcicontrolscan', 'comodopciscan']];

    /** where the client signs in with the login of the order (Sectigo knowledge base, 2026-11) */
    const LOGIN_URL = 'https://pci.qualys.com/merchant/';

    // contact fields the gateway takes (the same objects as a TLS order, no technical contact)
    const ORGANIZATION_FIELDS    = ['name', 'phone', 'address_line_1', 'address_line_2', 'city', 'region', 'postal_code', 'country'];
    const ORGANIZATION_REQUIRED  = ['name', 'address_line_1', 'city', 'country'];
    const ADMINISTRATOR_FIELDS   = ['title', 'first_name', 'last_name', 'email', 'phone'];
    const ADMINISTRATOR_REQUIRED = ['title', 'first_name', 'last_name', 'email', 'phone'];

    /** the vendor's answer for an organization name that is already registered */
    const NAME_TAKEN = '/company name already exists/i';

    public static function configurationTemplate()
    {
        return EmailTemplates::PCI_CONFIGURATION_TEMPLATE;
    }

    public static function isPciWhmcsProduct($whmcsProductId)
    {
        return static::isWhmcsProduct($whmcsProductId);
    }

    public static function isPciService($serviceId)
    {
        return static::isService($serviceId);
    }

    public static function isSupportedRemoteProduct($vendor, $category, $remoteId = '')
    {
        return strtolower((string)$category) === self::CATEGORY
            && in_array((string)$remoteId, self::PRODUCTS[strtoupper((string)$vendor)] ?? [], true);
    }

    // ------------------------------------------------------------------ order

    /**
     * Organization and administrator of the order in the gateway format
     *
     * @param array $missing filled with ['org'|'admin', field] when a required field is empty
     */
    public static function contacts(array $organization, array $administrator, &$missing = null)
    {
        $missing = null;
        $org     = [];
        foreach(self::ORGANIZATION_FIELDS as $field)
        {
            $value = self::clean($organization[$field] ?? '');
            if($value !== '')
            {
                $org[$field] = $field === 'country' ? strtoupper($value) : $value;
            }
        }
        $admin = [];
        foreach(self::ADMINISTRATOR_FIELDS as $field)
        {
            $value = self::clean($administrator[$field] ?? '');
            if($value !== '')
            {
                $admin[$field] = $value;
            }
        }

        foreach(self::ORGANIZATION_REQUIRED as $field)
        {
            if(!isset($org[$field]))
            {
                $missing = ['org', $field];

                return null;
            }
        }
        foreach(self::ADMINISTRATOR_REQUIRED as $field)
        {
            if(!isset($admin[$field]))
            {
                $missing = ['admin', $field];

                return null;
            }
        }

        return ['organization' => $org, 'administrator' => $admin];
    }

    /**
     * Places the order: the scan account is created at once and its login comes back in the answer
     */
    public static function create($serviceId, array $contacts)
    {
        [$service, $ssl] = static::requireOrderable($serviceId);

        $productId = (string)Capsule::table('tblproducts')->where('id', (int)$service->packageid)->value('configoption2');
        $term      = static::termForBillingCycle(AddonCertificates::billingCycleOf((int)$serviceId) ?? $service->billingcycle, static::availableTerms($service->packageid));
        $payload   = [
            'product'  => ['id' => SSLTrustCenterApi::productId($productId), 'term' => $term],
            'contacts' => $contacts,
        ];

        $response = static::placeLocked($ssl, function() use ($serviceId, $payload) {
            return CertificateService::api($serviceId)->createPci($payload);
        });

        $name   = (string)$contacts['organization']['name'];
        $stored = static::saveCreated($serviceId, $ssl, [
            'request'      => $payload,
            'term'         => $term,
            'organization' => $name,
            'contacts'     => $contacts,
        ], $response, $name, self::LABEL . ' scan account ordered for ' . $name);

        // the create answer has no scanning period: the details have it, and the next due date follows its end
        try
        {
            static::followPeriod($serviceId, static::refresh($serviceId));
        }
        catch(\Throwable $e)
        {
            CertificateService::event($serviceId, 'error', self::LABEL . ': the scanning period could not be read: ' . $e->getMessage());
        }

        return static::stored($serviceId) ?: $stored;
    }

    /** one order per service: a second submit (double click, two tabs) must not order again */
    protected static function placeLocked($ssl, callable $call)
    {
        $locked = Capsule::table('tblsslorders')->where('id', $ssl->id)->where('status', 'Awaiting Configuration')->update(['status' => 'Configuration Submitted']);
        if(!$locked)
        {
            throw new \Exception('The ' . self::LABEL . ' scan account has already been ordered');
        }
        try
        {
            return $call();
        }
        catch(\Exception $e)
        {
            // not ordered: the client can correct the data and submit again
            Capsule::table('tblsslorders')->where('id', $ssl->id)->update(['status' => 'Awaiting Configuration']);
            throw $e;
        }
    }

    /** next due date = the end of the scanning period (same rule and offset setting as the certificates) */
    protected static function followPeriod($serviceId, array $stored)
    {
        if(empty($stored['validTill']))
        {
            return;
        }
        $dueDate = SslProductHelpers::getServiceDueDate(['items' => [['subscription' => ['end' => $stored['validTill']]]]], []);
        if($dueDate)
        {
            Capsule::table('tblhosting')->where('id', (int)$serviceId)->update(['nextduedate' => $dueDate, 'nextinvoicedate' => $dueDate]);
            AddonCertificates::followDueDate((int)$serviceId, (string)$dueDate);
        }
    }

    /** the scan account has the organization's name: a name already registered at the scanning service */
    public static function isNameTaken($message)
    {
        return (bool)preg_match(self::NAME_TAKEN, (string)$message);
    }

    /** login of the scan control centre, [] when the order has none */
    public static function login($serviceId)
    {
        $login = (array)(static::stored($serviceId)['login'] ?? []);

        return !empty($login['username']) ? ['username' => (string)$login['username'], 'password' => (string)($login['password'] ?? '')] : [];
    }

    // ------------------------------------------------------------------ cancel / renewal

    /** PCI orders are cancelled as orders (POST /orders/{order id}/cancel): cancel and refund */
    public static function cancel($serviceId, $reason)
    {
        $stored = static::stored($serviceId);
        if(empty($stored['orderId']) || in_array($stored['status'] ?? '', array_diff(static::FINAL_STATUSES, ['active']), true))
        {
            return false;
        }

        CertificateService::api($serviceId)->cancelOrder((int)$stored['orderId'], $reason);

        $stored['status'] = 'cancelled';
        static::save($serviceId, $stored);
        Capsule::table('tblsslorders')->where('serviceid', (int)$serviceId)->update(['status' => 'Cancelled', 'updated_at' => date('Y-m-d H:i:s')]);

        return true;
    }

    /**
     * A paid renewal. A new order for the same organization is refused by the scanning service (the name stays
     * registered) and TheSSLStore has not said yet how a scan account is renewed, so nothing is ordered: the account
     * and its login stay, and the admin renews it by hand (logged as an error, so it shows on the module's log pages).
     *
     * @return string 'kept' or '' (nothing ordered yet)
     */
    public static function renewal($serviceId)
    {
        $stored = static::stored($serviceId);
        if(empty($stored['itemId']))
        {
            return '';
        }

        CertificateService::event($serviceId, 'error', self::LABEL . ' renewal paid for order #' . ($stored['orderId'] ?? '-')
            . ': the scan account can not be renewed automatically yet - renew it with %%VENDOR%% by hand');
        \logActivity('TSSGGS: ' . self::LABEL . ' renewal of service #' . (int)$serviceId . ' needs to be ordered by hand');

        return 'kept';
    }

    /** no Renew button: see renewal() */
    public static function renewalOpen($serviceId)
    {
        return false;
    }

    public static function canRenewWithSameDetails(array $stored)
    {
        return false;
    }

    // ------------------------------------------------------------------ hooks

    /** the login (only in the create answer) and the scanning period */
    protected static function applyItem(array $stored, array $item)
    {
        if(!empty($item['login']['username']))
        {
            $stored['login'] = [
                'username' => (string)$item['login']['username'],
                'password' => (string)($item['login']['password'] ?? ''),
            ];
        }

        return $stored;
    }

    /** "scan account ready" e-mail when the account is created (no password in the e-mail: it is on the service page) */
    protected static function onStatusChange($serviceId, $previousStatus, array $stored)
    {
        if(($stored['status'] ?? '') !== 'active' || $previousStatus !== '')
        {
            return;
        }

        EmailTemplates::sendEmail(EmailTemplates::PCI_READY_TEMPLATE, $serviceId, [
            'pci_organization' => (string)($stored['organization'] ?? ''),
            'pci_username'     => (string)($stored['login']['username'] ?? ''),
            'pci_login_url'    => self::LOGIN_URL,
        ]);
    }

    protected static function previousEntry(array $stored)
    {
        return ['organization' => $stored['organization'] ?? ''];
    }

    protected static function clean($value)
    {
        // WHMCS hands request data HTML-encoded (" arrives as &quot;): the gateway gets the plain text
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }
}
