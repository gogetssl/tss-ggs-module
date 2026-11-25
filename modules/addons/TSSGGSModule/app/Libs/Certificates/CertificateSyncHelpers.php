<?php

namespace TSSGGSModule\App\Libs\Certificates;

use Exception;
use TSSGGSModule\App\Models\Request;
use TSSGGSModule\App\Models\Whmcs\Service;
use TSSGGSModule\App\Libs\Logs\Logger;
use WHMCS\Database\Capsule;
use TSSGGSModule\App\Libs\Gateway\SSLTrustCenterApi;
use TSSGGSModule\App\Libs\Products\SslProductHelpers;
use TSSGGSModule\App\Libs\ProductLines\GatewayOrderService;
use TSSGGSModule\App\Libs\Support\Edition;

/**
 * Certificate details sync shared by the SSLCertificates cron (services reported by /services/updates)
 * and the admin "Sync Certificate" button (single service, without the updates request).
 */
class CertificateSyncHelpers
{
    /** statuses reported by /services/updates that need the order details: read with getOrder (+ files when active) */
    const FETCH_STATUSES = ['pending', 'processing', 'active'];

    /** statuses with no details to read: the order waits at the vendor side, the client page shows a warning */
    const NOTICE_STATUSES = ['failed', 'unpaid', 'new_order'];

    /** setting: every open order holds its certificate (item) ID, the one-time conversion is done */
    const ITEM_IDS_CONFIRMED = 'item_ids_confirmed';

    /** orders read per run by the conversion */
    const ITEM_ID_BATCH = 100;

    /** setting: service ID => when its order was first seen waiting for its validation method (JSON) */
    const AWAITING_CONFIGURATION = 'sync_awaiting_configuration';

    /** such an order is read every hour for its first day, then twice a day */
    const AWAITING_SLOW_AFTER = 86400;
    const AWAITING_SLOW_RETRY = 43200;

    /** service ID => first seen (unix time) */
    public static function awaitingConfiguration(): array
    {
        $value = Capsule::table('tbladdonmodules')->where('module', 'configuration_TSSGGSModule')->where('setting', self::AWAITING_CONFIGURATION)->value('value');
        $list  = $value ? json_decode((string)$value, true) : [];

        return is_array($list) ? $list : [];
    }

    public static function markAwaitingConfiguration(int $serviceId)
    {
        $list = self::awaitingConfiguration();
        if(!isset($list[$serviceId]))
        {
            $list[$serviceId] = time();
            self::storeAwaiting($list);
        }
    }

    public static function clearAwaitingConfiguration(int $serviceId)
    {
        $list = self::awaitingConfiguration();
        if(isset($list[$serviceId]))
        {
            unset($list[$serviceId]);
            self::storeAwaiting($list);
        }
    }

    protected static function storeAwaiting(array $list)
    {
        Capsule::table('tbladdonmodules')->updateOrInsert(['module' => 'configuration_TSSGGSModule', 'setting' => self::AWAITING_CONFIGURATION], ['value' => json_encode($list)]);
    }

    /**
     * One-time conversion to certificate (item) IDs. /services/updates reports item IDs; a service created before
     * 1.2.4, or moved over from an old module when only the order ID was known, holds the order ID in
     * tblsslorders.remoteid and is never matched. Such an order is read once (getOrder accepts both IDs) and the sync
     * stores its item ID. An order counts as converted when its stored order details name the same item ID.
     * When no open order is left to convert, the conversion stops for good (a migration starts it again).
     *
     * @param array $exclude remote IDs read in this run already
     * @return array rows from getCertificatesQuery() to read now
     */
    public static function itemIdConversionBatch(array $exclude)
    {
        $confirmed = Capsule::table('tbladdonmodules')->where('module', 'configuration_TSSGGSModule')->where('setting', self::ITEM_IDS_CONFIRMED)->value('value');
        if($confirmed === '1')
        {
            return [];
        }

        $left     = 0;
        $batch    = [];
        $awaiting = self::awaitingConfiguration();
        $open     = [];
        // the order row's last change comes with the query (one query, not one per open order)
        foreach(self::getCertificatesQuery()->addSelect('tblsslorders.updated_at as ssl_updated_at')->orderBy('tblsslorders.updated_at', 'asc')->get()->toArray() as $certificate)
        {
            $open[(int)$certificate['id']] = true;
            if(in_array((string)$certificate['remoteid'], $exclude, true) || !self::itemIdUnconfirmed($certificate))
            {
                continue;
            }
            $left++;
            // read again at most once an hour (an order that can not be read is not asked for on every run); an order
            // waiting for its validation method for more than a day only twice a day
            $since     = (int)($awaiting[(int)$certificate['id']] ?? 0);
            $retry     = date('Y-m-d H:i:s', time() - ($since && $since < time() - self::AWAITING_SLOW_AFTER ? self::AWAITING_SLOW_RETRY : 3600));
            $updatedAt = (string)($certificate['ssl_updated_at'] ?? '');
            if(count($batch) < self::ITEM_ID_BATCH && ($updatedAt === '' || $updatedAt < $retry))
            {
                $batch[] = $certificate;
            }
        }

        if($left === 0)
        {
            Capsule::table('tbladdonmodules')->updateOrInsert(['module' => 'configuration_TSSGGSModule', 'setting' => self::ITEM_IDS_CONFIRMED], ['value' => '1']);
        }

        // services no longer open (configured elsewhere, terminated, cancelled) leave the waiting list
        $kept = array_intersect_key($awaiting, $open);
        if(count($kept) !== count($awaiting))
        {
            self::storeAwaiting($kept);
        }

        return $batch;
    }

    /** a migration (or anything else that stores order IDs) starts the conversion again */
    public static function itemIdConversionRestart()
    {
        Capsule::table('tbladdonmodules')->where('module', 'configuration_TSSGGSModule')->where('setting', self::ITEM_IDS_CONFIRMED)->delete();
    }

    /** whether the stored order details do not confirm remoteid as the certificate (item) ID */
    protected static function itemIdUnconfirmed(array $certificate)
    {
        $stored = CertificateService::stored((int)$certificate['id']);
        $items  = isset($stored['orderData']['items']) && is_array($stored['orderData']['items']) ? $stored['orderData']['items'] : [];
        $item   = $items ? reset($items) : [];
        $itemId = (int)($item['id'] ?? 0);

        return $itemId <= 0 || (string)$itemId !== trim((string)$certificate['remoteid']);
    }

    /** one spelling for a status the API may write in two ways */
    public static function normalizeStatus($status)
    {
        $status = strtolower(trim((string)$status));

        return $status === 'canceled' ? 'cancelled' : $status;
    }

    /**
     * A TLS order reported by /services/updates with its new status: only "pending", "processing" and "active" are
     * read from the API (getOrder, and the certificate files when active); any other status is applied as it is
     *
     * @param array $certificate row from getCertificatesQuery()
     * @return string the status applied
     * @throws Exception
     */
    public static function applyReportedStatus(array $certificate, $status, array $addonConfig)
    {
        $status = self::normalizeStatus($status);
        if($status === '' || in_array($status, self::FETCH_STATUSES, true))
        {
            return self::syncCertificate($certificate, $addonConfig);
        }

        self::storeStatus((int)$certificate['id'], $status);

        return $status;
    }

    /**
     * Saves a status without reading the order: the stored order details stay, only their status changes. An order
     * that ended is handled like any sync does it (CertificateEnd: Cancelled / Terminated, e-mails).
     */
    public static function storeStatus($serviceId, $status)
    {
        $serviceId      = (int)$serviceId;
        $status         = self::normalizeStatus($status);
        $row            = Request::where('serviceid', $serviceId)->where('name', 'certificate')->first();
        $previousStatus = $row ? (string)$row->status : '';
        if($status === '' || $status === $previousStatus)
        {
            return;
        }

        Request::where('serviceid', $serviceId)->where('name', 'addOrder')->update(['status' => $status]);
        if($row)
        {
            $stored = !empty($row->request) ? (json_decode(\decrypt($row->request), true) ?: []) : [];
            $stored['orderData']['order']['status'] = $status;
            Request::where('serviceid', $serviceId)->where('name', 'certificate')->update([
                'request'    => \encrypt(json_encode($stored)),
                'status'     => $status,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        Capsule::table('tblsslorders')->where('serviceid', $serviceId)->update(['updated_at' => date('Y-m-d H:i:s')]);

        CertificateEnd::apply($serviceId, $previousStatus, $status);

        Logger::info('Certificate status: ' . ($previousStatus ?: 'new') . ' -> ' . $status . ' (reported by the vendor, order not read)', ['service' => $serviceId, 'from' => $previousStatus, 'to' => $status]);
    }

    /**
     * Services which can be synchronized: active, module TSSGGSModule, order submitted to the API
     */
    public static function getCertificatesQuery()
    {
        // certificates kept as a record of the legacy module are not synced (App\Libs\Certificates\RecordOnly)
        return RecordOnly::exclude(Service::select(['tblhosting.*', 'tblsslorders.remoteid', 'tblproducts.configoption1', 'tblproducts.configoption9'])
            ->join('tblsslorders', 'tblsslorders.serviceid', '=', 'tblhosting.id')
            ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
            ->where('tblproducts.servertype', 'TSSGGSModule')
            ->where('tblhosting.domainstatus', 'Active')
            ->where('tblsslorders.status', 'Configuration Submitted')
            // S/MIME and code signing orders have their own data and refresh (GatewayOrderService)
            ->where(function($query) {
                // S/MIME, code signing and the subscriptions (ACME, Plan + Automate) have their own sync
                $query->whereNull('tblproducts.configoption9')->orWhereNotIn('tblproducts.configoption9', array_merge(GatewayOrderService::categories(), SSLTrustCenterApi::SUBSCRIPTION_CATEGORIES));
            }));
    }

    /**
     * @param array $certificate row from getCertificatesQuery()
     * @param array $addonConfig AddonModuleRepository::getModuleConfiguration()
     * @return string remote order status
     * @throws Exception
     */
    public static function syncCertificate(array $certificate, array $addonConfig)
    {
        $api       = SSLTrustCenterApi::forVendor($certificate['configoption1'], $certificate['configoption9']);
        $orderData = $api->getOrder($certificate['remoteid'], $certificate['id']);
        // an empty answer must never replace what is stored
        if(empty($orderData['order']['status']))
        {
            throw new Exception(Edition::brandOf($certificate['configoption1'] ?? '') . ' returned no status for order ' . $certificate['remoteid']);
        }

        // Services configured before 1.2.4 store the API order ID in tblsslorders.remoteid; since 1.2.4 it is the
        // certificate (item) ID. The API accepts both, so migrate the stored value whenever the order is fetched.
        $itemId = SSLTrustCenterApi::getItemId($orderData, $certificate['remoteid']);
        Capsule::table('tblsslorders')->where('serviceid', $certificate['id'])->update([
            'remoteid'   => $itemId,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $certificate['remoteid'] = $itemId;

        $orderFiles = [];
        if($orderData['order']['status'] == 'active') {
            try {
                $orderFiles = $api->getCertificateFiles($certificate['remoteid']);
            } catch (Exception $exception) {
                Logger::error('Get Certificate Files Error:' . $exception->getMessage(), \TSSGGSModule\App\Libs\Support\LogContext::exception($exception, ['service' => $certificate['id']]));
            }
        }

        Request::where('serviceid', $certificate['id'])->where('name', 'addOrder')->update([
            'status' => $orderData['order']['status']
        ]);

        // the files could not be read this time: an issued certificate keeps the files it already has
        if($orderData['order']['status'] == 'active' && !$orderFiles)
        {
            $kept = CertificateService::stored($certificate['id']);
            if(!empty($kept['orderFiles']) && ($kept['orderData']['order']['status'] ?? '') === 'active')
            {
                $orderFiles = $kept['orderFiles'];
            }
        }

        $request = ['orderData' => $orderData, 'orderFiles' => $orderFiles];

        $previousStatus = Request::where('serviceid', $certificate['id'])->where('name', 'certificate')->value('status');
        Request::where('serviceid', $certificate['id'])->where('name', 'certificate')->delete();
        Request::insert([
            'serviceid' => $certificate['id'],
            'name' => 'certificate',
            'request' => \encrypt(json_encode($request)),
            'status' => $orderData['order']['status'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // the CA's own CAA check failed: logged once
        try
        {
            CaaCheck::afterSync((int)$certificate['id'], $orderData);
        }
        catch(\Throwable $e)
        {
        }

        if($orderData['order']['status'] == 'active') {
            // the order end (never the certificate end) - certificates within the order are reissued
            $nextDueDate = SslProductHelpers::getServiceDueDate($orderData, $orderFiles);
            if($nextDueDate) {
                Service::where('id', $certificate['id'])->update([
                    'nextinvoicedate' => $nextDueDate,
                    'nextduedate' => $nextDueDate,
                ]);
                // a product addon's certificate: the addon is billed on the same date
                AddonCertificates::followDueDate((int)$certificate['id'], (string)$nextDueDate);
            }
        }

        // cancelled / rejected / revoked / refunded: Cancelled; expired / filed: Terminated with a date - plus the
        // client e-mails of a revocation or an expiry (App\Libs\Certificates\CertificateEnd)
        CertificateEnd::apply((int)$certificate['id'], (string)$previousStatus, (string)$orderData['order']['status']);

        CertificateService::notifyIssued($certificate['id'], $previousStatus, $orderData['order']['status']);
        if($orderData['order']['status'] === 'active')
        {
            CertificateService::forgetPreviousCertificate($certificate['id']);
        }

        if($previousStatus !== $orderData['order']['status'])
        {
            Logger::info('Certificate status: ' . ($previousStatus ?: 'new') . ' -> ' . $orderData['order']['status'], ['service' => $certificate['id'], 'from' => $previousStatus, 'to' => $orderData['order']['status']]);
        }

        return $orderData['order']['status'];
    }
}
