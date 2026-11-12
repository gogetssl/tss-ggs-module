<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TSSGGSModule\App\Models\Request;
use WHMCS\Database\Capsule;

/**
 * Certificates the module orders through the gateway outside the SSL flow (S/MIME, code signing): one order per
 * service, kept in the module's Requests table (name = REQUEST_NAME, encrypted JSON).
 *
 * Stored keys: orderId, itemId, status, vendorOrderId, validFrom, validTill, term, orderedAt, refreshedAt,
 * previous [{orderId, itemId, status, validTill, ...}] (orders before a renewal) plus the keys of the product type.
 * tblsslorders: remoteid = item ID, status Awaiting Configuration -> Configuration Submitted -> Completed / Cancelled.
 *
 * A product type sets CATEGORY, REQUEST_NAME, LABEL and overrides the hooks (applyItem, onStatusChange,
 * afterCronRefresh, previousEntry, quietFailure).
 */
abstract class GatewayOrderService
{
    const CATEGORY     = '';
    const REQUEST_NAME = '';
    const LABEL        = '';

    // the order will not change any more: no automatic refresh
    const FINAL_STATUSES = ['active', 'expired', 'cancelled', 'failed', 'rejected', 'revoked', 'refunded'];

    /** product types: gateway category => class */
    const TYPES = ['smime' => SmimeService::class, 'cs' => CodeSigningService::class];

    /** @var array "category:product ID" => bool */
    protected static $products = [];

    /** Gateway categories handled outside the SSL flow */
    public static function categories()
    {
        return array_keys(self::TYPES);
    }

    /**
     * Product type class of the WHMCS product (null: an SSL / ACME / AutoInstall product)
     *
     * @return string|null class name
     */
    public static function typeOfProduct($whmcsProductId)
    {
        foreach(self::TYPES as $class)
        {
            if($class::isWhmcsProduct($whmcsProductId))
            {
                return $class;
            }
        }

        return null;
    }

    /**
     * @return string|null class name
     */
    public static function typeOfService($serviceId)
    {
        $packageId = (int)Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('packageid');

        return $packageId ? self::typeOfProduct($packageId) : null;
    }

    public static function isWhmcsProduct($whmcsProductId)
    {
        $key = static::CATEGORY . ':' . (int)$whmcsProductId;
        if(!isset(self::$products[$key]))
        {
            $category = Capsule::table('tblproducts')->where('id', (int)$whmcsProductId)->value('configoption9');
            self::$products[$key] = strtolower((string)$category) === static::CATEGORY;
        }

        return self::$products[$key];
    }

    public static function isService($serviceId)
    {
        $packageId = (int)Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('packageid');

        return $packageId && static::isWhmcsProduct($packageId);
    }

    /**
     * Gateway product of the WHMCS product (name, validation, prices, product data)
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
     * Terms (months) the gateway sells for the product
     */
    public static function availableTerms($whmcsProductId)
    {
        $remote = static::remoteProduct($whmcsProductId);
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
        $row = Request::where('serviceid', (int)$serviceId)->where('name', static::REQUEST_NAME)->first();
        if(!$row || empty($row->request))
        {
            return [];
        }

        $data = json_decode((string)\decrypt($row->request), true);

        return is_array($data) ? $data : [];
    }

    public static function isOrdered($serviceId)
    {
        return !empty(static::stored($serviceId)['itemId']);
    }

    protected static function save($serviceId, array $data)
    {
        $values = [
            'request'    => \encrypt(json_encode($data)),
            'status'     => (string)($data['status'] ?? ''),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if(Request::where('serviceid', (int)$serviceId)->where('name', static::REQUEST_NAME)->exists())
        {
            Request::where('serviceid', (int)$serviceId)->where('name', static::REQUEST_NAME)->update($values);
        }
        else
        {
            $request            = new Request();
            $request->serviceid = (int)$serviceId;
            $request->name      = static::REQUEST_NAME;
            $request->request   = $values['request'];
            $request->status    = $values['status'];
            $request->save();
        }
    }

    /**
     * Service, product and SSL order of an order about to be placed (the checks every product type makes)
     *
     * @return array [service row, tblsslorders row]
     */
    protected static function requireOrderable($serviceId)
    {
        $service = Capsule::table('tblhosting')->where('id', (int)$serviceId)->first(['id', 'userid', 'packageid', 'billingcycle', 'domainstatus', 'amount']);
        if(!$service || !static::isWhmcsProduct($service->packageid))
        {
            throw new \Exception('This service is not a ' . static::LABEL . ' certificate');
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
        if(static::isOrdered($serviceId) || $ssl->status !== 'Awaiting Configuration')
        {
            throw new \Exception('The ' . static::LABEL . ' certificate has already been ordered');
        }

        return [$service, $ssl];
    }

    /**
     * Saves a placed order: request row, SSL order (item ID, status), service domain, cost, activity
     *
     * @param array $stored the product type's keys (term, emails, delivery...)
     */
    protected static function saveCreated($serviceId, $ssl, array $stored, $response, $domain, $logMessage)
    {
        $orderId = (int)($response['order']['id'] ?? 0);
        $itemId  = (int)($response['items'][0]['id'] ?? 0);
        if($orderId <= 0 || $itemId <= 0)
        {
            throw new \Exception('The gateway did not return an order ID');
        }

        $stored = static::applyDetails($stored + [
            'orderId'   => $orderId,
            'itemId'    => $itemId,
            'orderedAt' => date('Y-m-d H:i:s'),
            'previous'  => static::stored($serviceId)['previous'] ?? [],
        ], $response);
        static::save($serviceId, $stored);

        Capsule::table('tblsslorders')->where('id', $ssl->id)->update([
            'remoteid'   => $itemId,
            'status'     => ($stored['status'] ?? '') === 'active' ? 'Completed' : 'Configuration Submitted',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        Capsule::table('tblhosting')->where('id', (int)$serviceId)->update(['domain' => mb_substr((string)$domain, 0, 255)]);
        static::saveCost($serviceId, (float)($response['order']['total_amount'] ?? 0));

        CertificateService::event($serviceId, 'info', $logMessage . ' (order #' . $orderId . ')');
        if(($stored['status'] ?? '') === 'active')
        {
            static::onStatusChange($serviceId, '', $stored);
        }

        return $stored;
    }

    /**
     * Reads the order from the gateway and saves it
     */
    public static function refresh($serviceId)
    {
        $stored = static::stored($serviceId);
        if(empty($stored['itemId']))
        {
            throw new \Exception('The ' . static::LABEL . ' certificate has not been ordered yet');
        }

        $previousStatus = (string)($stored['status'] ?? '');
        $response       = CertificateService::api($serviceId)->getCertificateDetails(static::CATEGORY, (int)$stored['itemId']);
        $stored         = static::applyDetails($stored, $response);
        $stored['refreshedAt'] = date('Y-m-d H:i:s');
        static::save($serviceId, $stored);

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

            CertificateService::event($serviceId, in_array($status, ['failed', 'rejected'], true) ? 'error' : 'info', static::LABEL . ' certificate status: ' . ($previousStatus ?: '-') . ' -> ' . $status);
            static::onStatusChange($serviceId, $previousStatus, $stored);
        }

        return $stored;
    }

    /**
     * The client page reads the order from the gateway when it was last read more than $seconds ago
     */
    public static function refreshDue(array $stored, $seconds = 60)
    {
        if(empty($stored['itemId']) || in_array($stored['status'] ?? '', static::FINAL_STATUSES, true))
        {
            return false;
        }

        return empty($stored['refreshedAt']) || strtotime($stored['refreshedAt']) < time() - (int)$seconds;
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
            ->where('tblproducts.configoption9', static::CATEGORY)
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
                $stored = static::stored($serviceId);
                if(!empty($stored['itemId']) && !in_array($stored['status'] ?? '', static::FINAL_STATUSES, true))
                {
                    static::refresh($serviceId);
                    static::afterCronRefresh($serviceId);
                }
            }
            catch(\Throwable $e)
            {
                if(!static::quietFailure(static::stored($serviceId)))
                {
                    $failed[(int)$serviceId] = $e->getMessage();
                }
            }
        }

        return $failed;
    }

    /**
     * Cancels the order at the gateway (Terminate, admin "Cancel Order"). False when there is nothing to cancel.
     */
    public static function cancel($serviceId, $reason)
    {
        $stored = static::stored($serviceId);
        // an issued (active) certificate can still be cancelled; the other final states can not
        if(empty($stored['itemId']) || in_array($stored['status'] ?? '', array_diff(static::FINAL_STATUSES, ['active']), true))
        {
            return false;
        }

        CertificateService::api($serviceId)->cancelItem((int)$stored['itemId'], $reason, static::CATEGORY);

        $stored['status'] = 'cancelled';
        static::save($serviceId, $stored);
        Capsule::table('tblsslorders')->where('serviceid', (int)$serviceId)->update(['status' => 'Cancelled', 'updated_at' => date('Y-m-d H:i:s')]);

        return true;
    }

    /**
     * Renewal: the certificate is not renewed in place - the service goes back to Awaiting Configuration and the
     * client orders the next certificate (the current one stays valid until it expires).
     *
     * @return bool true when the service was reset (false: nothing ordered yet)
     */
    public static function resetForRenewal($serviceId)
    {
        $stored = static::stored($serviceId);
        if(empty($stored['itemId']))
        {
            return false;
        }

        $previous   = (array)($stored['previous'] ?? []);
        $previous[] = static::previousEntry($stored) + [
            'orderId'   => $stored['orderId'] ?? null,
            'itemId'    => $stored['itemId'],
            'status'    => $stored['status'] ?? '',
            'validTill' => $stored['validTill'] ?? '',
        ];
        static::save($serviceId, ['status' => 'renewal', 'previous' => array_slice($previous, -10)]);

        Capsule::table('tblsslorders')->where('serviceid', (int)$serviceId)->update([
            'remoteid'   => '',
            'status'     => 'Awaiting Configuration',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        CertificateService::event($serviceId, 'info', static::LABEL . ' renewal: the service waits for the next certificate order (previous order #' . ($stored['orderId'] ?? '-') . ')');

        return true;
    }

    /**
     * Gateway cost, WHMCS price and invoice of the order on the request row (Reports: cost and profit)
     */
    protected static function saveCost($serviceId, $apiPrice)
    {
        $service   = Capsule::table('tblhosting')->where('id', (int)$serviceId)->first(['amount', 'firstpaymentamount']);
        $invoiceId = (int)Capsule::table('tblinvoiceitems')->where('relid', (int)$serviceId)->where('type', 'Hosting')->orderBy('invoiceid', 'desc')->value('invoiceid');
        $rate      = (float)((new \ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository())->getModuleConfiguration()['financeSettings']['rate'] ?? 1) ?: 1;
        $amount    = $service ? (float)$service->amount : 0;

        Request::where('serviceid', (int)$serviceId)->where('name', static::REQUEST_NAME)->update([
            'invoiceid'   => $invoiceId,
            'api_price'   => $apiPrice,
            'whmcs_price' => $amount,
            'rate'        => $rate,
            'diff_price'  => $amount - $apiPrice,
        ]);
    }

    /**
     * Status, order ID, CA order ID and validity from a create / details response
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

        return static::applyItem($stored, $item);
    }

    /** E-mail template key of the "order details required" e-mail (new service, renewal) */
    public static function configurationTemplate()
    {
        return '';
    }

    // ------------------------------------------------------------------ hooks of the product types

    /** Product type's keys from the response item */
    protected static function applyItem(array $stored, array $item)
    {
        return $stored;
    }

    /** The status changed (also on create when the order is issued at once): e-mails */
    protected static function onStatusChange($serviceId, $previousStatus, array $stored)
    {
    }

    /** After a cron refresh of an open order: reminders */
    protected static function afterCronRefresh($serviceId)
    {
    }

    /** Keys kept for an order replaced by a renewal */
    protected static function previousEntry(array $stored)
    {
        return [];
    }

    /** A refresh error the cron does not report */
    protected static function quietFailure(array $stored)
    {
        return false;
    }
}
