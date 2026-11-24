<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Models\Request;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\ProductRepository;
use ModulesGarden\TSSGGSModule\Packages\Logs\Support\Facades\Logger;
use WHMCS\Database\Capsule;

/**
 * A TLS certificate bought at payment, without its details (POST /certificates/tls/incomplete). The gateway returns
 * the certificate token: it completes the order (POST /certificates/tls/{token}, the CSR, the names and the contacts)
 * and manages it afterwards - from this module's configuration page or from any plugin the client gives the token to
 * (cPanel, Plesk ...).
 *
 * Stored in the Requests row "incompleteOrder" (JSON, plain: the token is shown to the client and the admin as it is):
 *  item_id, order_id, token, single, wildcard (the SANs bought), created_at. Its status is "incomplete" until the
 *  order is completed (here or elsewhere), then "completed". tblsslorders.remoteid stays empty until then, so
 *  everything that checks for an ordered certificate keeps working.
 */
class IncompleteOrder
{
    const REQUEST = 'incompleteOrder';

    const STATUS_INCOMPLETE = 'incomplete';
    const STATUS_COMPLETED  = 'completed';

    /** a migrated order whose token could not be read yet (claimMigrated() tries again an hour later) */
    const STATUS_UNREAD = 'unread';

    /** migrated orders claimed per cron run */
    const CLAIM_BATCH = 50;

    /**
     * Whether the product's certificate is bought at payment with a token: TLS products of the module (not the
     * subscriptions, S/MIME, code signing and the other gateway products)
     */
    public static function appliesTo($packageId)
    {
        $packageId = (int)$packageId;
        if(AcmeHelpers::isAcmeWhmcsProduct($packageId) || GatewayOrderService::typeOfProduct($packageId)
            || SubscriptionBilling::isSubscriptionProduct($packageId))
        {
            return false;
        }
        $product = Capsule::table('tblproducts')->where('id', $packageId)->first(['servertype', 'configoption9']);
        if(!$product || $product->servertype !== 'TSSGGSModule')
        {
            return false;
        }
        $category = (string)$product->configoption9;

        return $category === '' || $category === 'tls';
    }

    /** the stored record, null when the service has none */
    public static function get($serviceId)
    {
        $row = Request::where('serviceid', (int)$serviceId)->where('name', self::REQUEST)->first();
        if(!$row || empty($row->request))
        {
            return null;
        }
        $data = json_decode((string)$row->request, true);
        if(!is_array($data))
        {
            return null;
        }
        $data['status'] = (string)$row->status;

        return $data;
    }

    /** the record of an order bought and not completed yet, null otherwise */
    public static function pending($serviceId)
    {
        $data = self::get($serviceId);

        return $data && $data['status'] === self::STATUS_INCOMPLETE && !empty($data['token']) ? $data : null;
    }

    /** the token to show while the order waits for its details ('' when there is none) */
    public static function token($serviceId)
    {
        $data = self::pending($serviceId);

        return $data ? (string)$data['token'] : '';
    }

    /**
     * Term of the certificate in months: the term set on the Products page, else the billing cycle (one year for
     * the cycles shorter than a year)
     */
    public static function term($packageId, $billingCycle)
    {
        $months = (int)(ProductSettings::get($packageId)['months'] ?? 0);
        if($months > 0)
        {
            return $months;
        }

        switch((string)$billingCycle)
        {
            case 'Biennially':
                return 24;
            case 'Triennially':
                return 36;
            default:
                return 12;
        }
    }

    /**
     * The incomplete order of the service, bought now when there is none waiting. Throws when the gateway refuses it
     * (no balance, unknown product ...): nothing is stored then.
     *
     * @param array  $sans         ['single' => n, 'wildcard' => n] - the SANs paid with the service
     * @param string $purchaseRow  Requests row of the purchase: addOrder (the first order) or renewOrder (a renewal on
     *                             the same service: the rows of the earlier orders stay)
     */
    public static function ensure($serviceId, $packageId, $billingCycle, array $sans, $purchaseRow = 'addOrder')
    {
        $pending = self::pending($serviceId);
        if($pending)
        {
            return $pending;
        }

        $configProduct = (new ProductRepository())->getProductConfiguration($packageId);
        $api           = SSLTrustCenterApi::forVendor($configProduct['provider'], $configProduct['category']);
        // a product addon's certificate: its term follows the addon's billing cycle (the service itself is free)
        $billingCycle  = AddonCertificates::billingCycleOf((int)$serviceId) ?? $billingCycle;

        $data = [
            'product' => [
                'id'   => $configProduct['product_id'],
                'term' => self::term($packageId, $billingCycle),
            ],
            'domains' => [
                'single'   => max(0, (int)($sans['single'] ?? 0)),
                'wildcard' => max(0, (int)($sans['wildcard'] ?? 0)),
            ],
        ];

        $response = $api->createIncomplete($data, $serviceId);
        $items    = isset($response['items']) && is_array($response['items']) ? $response['items'] : [];
        $item     = $items ? reset($items) : [];
        $itemId   = (int)($item['id'] ?? 0);
        $token    = trim((string)($item['token'] ?? ''));
        if($itemId <= 0 || $token === '')
        {
            throw new \Exception(Edition::brandOf($configProduct['provider']) . ' did not return the certificate token');
        }

        $record = [
            'item_id'    => $itemId,
            'order_id'   => (int)($response['order']['id'] ?? 0),
            'token'      => $token,
            'single'     => $data['domains']['single'],
            'wildcard'   => $data['domains']['wildcard'],
            'created_at' => date('Y-m-d H:i:s'),
        ];
        Request::updateOrInsert(
            ['serviceid' => (int)$serviceId, 'name' => self::REQUEST],
            ['request' => json_encode($record), 'status' => self::STATUS_INCOMPLETE, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]
        );

        // the purchase of the certificate: the order reports and prices read this row (completed with the details later)
        if($purchaseRow === 'addOrder')
        {
            Request::where('serviceid', (int)$serviceId)->where('name', 'addOrder')->delete();
        }
        Request::insert([
            'serviceid'  => (int)$serviceId,
            'name'       => $purchaseRow === 'renewOrder' ? 'renewOrder' : 'addOrder',
            'request'    => \encrypt(json_encode($data)),
            'api_price'  => (float)($response['order']['total_amount'] ?? 0),
            'status'     => (string)($response['order']['status'] ?? self::STATUS_INCOMPLETE),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        CertificateService::event($serviceId, 'success', '[Order] Certificate bought, waiting for its details (certificate #' . $itemId
            . ', order #' . $record['order_id'] . ')');

        return $record + ['status' => self::STATUS_INCOMPLETE];
    }

    /** the order got its details (here or elsewhere) */
    public static function markCompleted($serviceId)
    {
        Request::where('serviceid', (int)$serviceId)->where('name', self::REQUEST)->update(['status' => self::STATUS_COMPLETED, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Whether the gateway's order details show a completed order: it has its domains (GGS: status no longer
     * "incomplete"; TSS: the invite was placed as an order)
     */
    public static function isCompleted(array $orderData)
    {
        $items   = isset($orderData['items']) && is_array($orderData['items']) ? $orderData['items'] : [];
        $item    = $items ? reset($items) : [];
        $domains = isset($item['domains']) && is_array($item['domains']) ? $item['domains'] : [];
        $status  = strtolower((string)($orderData['order']['status'] ?? ''));

        return !empty($domains) && $status !== 'incomplete';
    }

    /**
     * An order completed outside this module (a plugin with the token): the service takes it over - Configuration
     * Submitted, the order details stored, the main domain set. Nothing happens while the order is still incomplete.
     *
     * @return bool whether the service took the order over
     */
    public static function adoptIfCompleted($serviceId)
    {
        $pending = self::pending($serviceId);
        $ssl     = Capsule::table('tblsslorders')->where('serviceid', (int)$serviceId)->first();
        if(!$pending || !$ssl || $ssl->status !== 'Awaiting Configuration')
        {
            return false;
        }

        $api       = CertificateService::api($serviceId);
        $orderData = $api->getOrder($pending['item_id'], $serviceId);
        if(!self::isCompleted($orderData))
        {
            return false;
        }

        self::takeOver($serviceId, $pending, $orderData);
        CertificateService::event($serviceId, 'info', '[Order] The certificate details were submitted with the certificate token outside WHMCS: the service follows the order now');

        return true;
    }

    /**
     * The completed order becomes the service's order (shared by the configuration page and adoptIfCompleted())
     */
    public static function takeOver($serviceId, array $pending, array $orderData)
    {
        $itemId     = SSLTrustCenterApi::getItemId($orderData, $pending['item_id']);
        $status     = (string)($orderData['order']['status'] ?? 'processing');
        // the main name: from the details submitted here, else the order's first domain (submitted elsewhere)
        $items      = isset($orderData['items']) && is_array($orderData['items']) ? $orderData['items'] : [];
        $item       = $items ? reset($items) : [];
        $domains    = isset($item['domains']) && is_array($item['domains']) ? $item['domains'] : [];
        $firstName  = $domains ? (string)(reset($domains)['name'] ?? '') : '';
        $commonName = $orderData['orderData']['common_name']['name'] ?? ($item['common_name']['name'] ?? ($item['common_name'] ?? $firstName));
        $commonName = is_string($commonName) && $commonName !== '' ? $commonName : $firstName;

        Capsule::table('tblsslorders')->where('serviceid', (int)$serviceId)->update([
            'remoteid'       => $itemId,
            'status'         => 'Configuration Submitted',
            'completiondate' => date('Y-m-d H:i:s'),
        ]);
        if($commonName !== '')
        {
            Capsule::table('tblhosting')->where('id', (int)$serviceId)->update(['domain' => $commonName]);
        }
        Request::where('serviceid', (int)$serviceId)->where('name', 'addOrder')->update(['status' => $status]);
        Request::where('serviceid', (int)$serviceId)->where('name', 'certificate')->delete();
        Request::insert([
            'serviceid'  => (int)$serviceId,
            'name'       => 'certificate',
            'request'    => \encrypt(json_encode(['orderData' => $orderData, 'orderFiles' => []])),
            'status'     => $status,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        SanUpgrade::rememberPurchased($serviceId, ['single' => (int)($pending['single'] ?? 0), 'wildcard' => (int)($pending['wildcard'] ?? 0)]);
        self::markCompleted($serviceId);
    }

    /**
     * The services waiting with an incomplete order whose certificate /services/updates reported: those completed
     * elsewhere are taken over
     *
     * @param array $itemIds certificate (item) IDs reported as changed
     * @return array service ID => error message of the ones that could not be read
     */
    public static function adoptReported(array $itemIds)
    {
        $failed = [];
        if(!$itemIds)
        {
            return $failed;
        }
        $wanted = array_flip(array_map('strval', $itemIds));
        foreach(Request::where('name', self::REQUEST)->where('status', self::STATUS_INCOMPLETE)->get(['serviceid', 'request']) as $row)
        {
            $data = json_decode((string)$row->request, true);
            if(!is_array($data) || !isset($wanted[(string)($data['item_id'] ?? '')]))
            {
                continue;
            }
            try
            {
                self::adoptIfCompleted((int)$row->serviceid);
            }
            catch(\Throwable $e)
            {
                $failed[(int)$row->serviceid] = $e->getMessage();
                Logger::error('Incomplete order: ' . $e->getMessage(), LogContext::exception($e, ['service' => (int)$row->serviceid]));
            }
        }

        return $failed;
    }

    /**
     * A TLS order migrated from the legacy TheSSLStore module that was bought at payment there (Awaiting Configuration,
     * the store's order ID in tblsslorders.remoteid). Its order details carry the certificate token until the order is
     * completed: the token is read once and the order becomes an incomplete order like one bought here (remoteid empty
     * again, so the configuration page completes it with the token). An order completed meanwhile (with the token, in
     * a plugin) is taken over. A failed read is tried again an hour later.
     *
     * @return bool whether the service holds an incomplete order or took the order over now
     */
    public static function claimMigrated($serviceId)
    {
        $serviceId = (int)$serviceId;
        $ssl       = Capsule::table('tblsslorders')->where('serviceid', $serviceId)->first(['status', 'remoteid']);
        $remoteId  = $ssl ? trim((string)$ssl->remoteid) : '';
        if(!$ssl || $ssl->status !== 'Awaiting Configuration' || $remoteId === '' || $remoteId === '0')
        {
            return false;
        }
        $record = self::get($serviceId);
        if($record && ($record['status'] !== self::STATUS_UNREAD || self::readRecently($record)))
        {
            return false;
        }
        if(!self::appliesTo((int)Capsule::table('tblhosting')->where('id', $serviceId)->value('packageid')))
        {
            return false;
        }

        try
        {
            $orderData = CertificateService::api($serviceId)->getOrder($remoteId, $serviceId);
            $items     = isset($orderData['items']) && is_array($orderData['items']) ? $orderData['items'] : [];
            $item      = $items ? reset($items) : [];
            $itemId    = (int)($item['id'] ?? 0);
            $token     = trim((string)($item['token'] ?? ''));
            if($itemId <= 0)
            {
                throw new \Exception('the order details name no certificate');
            }
            $sans   = SanUpgrade::currentQuantities($serviceId);
            $record = [
                'item_id'    => $itemId,
                'order_id'   => (int)($orderData['order']['id'] ?? 0),
                'token'      => $token,
                'single'     => (int)($sans['single'] ?? 0),
                'wildcard'   => (int)($sans['wildcard'] ?? 0),
                'created_at' => date('Y-m-d H:i:s'),
                'migrated'   => $remoteId,
            ];

            if(self::isCompleted($orderData))
            {
                self::takeOver($serviceId, $record, $orderData);
                CertificateService::event($serviceId, 'info', '[Order] The migrated order was completed outside WHMCS (with its certificate token): the service follows the order now');

                return true;
            }
            if($token === '')
            {
                throw new \Exception('the order details hold no certificate token');
            }

            Request::updateOrInsert(
                ['serviceid' => $serviceId, 'name' => self::REQUEST],
                ['request' => json_encode($record), 'status' => self::STATUS_INCOMPLETE, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]
            );
            // the purchase made by the legacy module: the order reports and prices read this row
            if(!Request::where('serviceid', $serviceId)->where('name', 'addOrder')->exists())
            {
                Request::insert([
                    'serviceid'  => $serviceId,
                    'name'       => 'addOrder',
                    'request'    => \encrypt(json_encode(['migrated' => $remoteId])),
                    'api_price'  => (float)($orderData['order']['total_amount'] ?? 0),
                    'status'     => (string)($orderData['order']['status'] ?? self::STATUS_INCOMPLETE),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
            Capsule::table('tblsslorders')->where('serviceid', $serviceId)->update(['remoteid' => '']);
            CertificateService::event($serviceId, 'info', '[Order] Migrated order bought at payment: its certificate token was read (certificate #' . $itemId . ')');

            return true;
        }
        catch(\Throwable $e)
        {
            Request::updateOrInsert(
                ['serviceid' => $serviceId, 'name' => self::REQUEST],
                ['request' => json_encode(['migrated' => $remoteId, 'checked_at' => date('Y-m-d H:i:s'), 'error' => $e->getMessage()]),
                 'status' => self::STATUS_UNREAD, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]
            );
            Logger::error('Migrated order ' . $remoteId . ': the certificate token could not be read: ' . $e->getMessage(), LogContext::exception($e, ['service' => $serviceId]));

            return false;
        }
    }

    /** a failed token read of a migrated order is tried again an hour later */
    protected static function readRecently(array $record)
    {
        return !empty($record['checked_at']) && strtotime((string)$record['checked_at']) > time() - 3600;
    }

    /**
     * Migrated orders bought at payment whose token is not read yet (cron): claimMigrated() for a batch per run
     *
     * @return array service ID => error message of the ones that could not be read
     */
    public static function claimMigratedBatch()
    {
        $failed     = [];
        $serviceIds = Capsule::table('tblsslorders')
            ->join('tblhosting', 'tblhosting.id', '=', 'tblsslorders.serviceid')
            ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->where('tblproducts.servertype', 'TSSGGSModule')
            ->where('tblhosting.domainstatus', 'Active')
            ->where('tblsslorders.status', 'Awaiting Configuration')
            ->whereNotNull('tblsslorders.remoteid')
            ->whereNotIn('tblsslorders.remoteid', ['', '0'])
            ->orderBy('tblsslorders.id')
            ->pluck('tblsslorders.serviceid');

        $read = 0;
        foreach($serviceIds as $serviceId)
        {
            $record = self::get((int)$serviceId);
            if($read >= self::CLAIM_BATCH || ($record && ($record['status'] !== self::STATUS_UNREAD || self::readRecently($record))))
            {
                continue;
            }
            $read++;
            if(!self::claimMigrated((int)$serviceId))
            {
                $after = self::get((int)$serviceId);
                if($after && $after['status'] === self::STATUS_UNREAD && !empty($after['error']))
                {
                    $failed[(int)$serviceId] = 'Certificate token of the migrated order not read: ' . $after['error'];
                }
            }
        }

        return $failed;
    }

    /**
     * Cancels the incomplete order at the vendor (the service was cancelled or terminated before the details were
     * submitted). Returns whether there was one to cancel.
     */
    public static function cancel($serviceId, $reason)
    {
        $pending = self::pending($serviceId);
        if(!$pending)
        {
            return false;
        }
        CertificateService::api($serviceId)->cancelItem((int)$pending['item_id'], $reason);
        Request::where('serviceid', (int)$serviceId)->where('name', self::REQUEST)->update(['status' => 'cancelled', 'updated_at' => date('Y-m-d H:i:s')]);
        Request::where('serviceid', (int)$serviceId)->where('name', 'addOrder')->update(['status' => 'cancelled']);

        return true;
    }
}
