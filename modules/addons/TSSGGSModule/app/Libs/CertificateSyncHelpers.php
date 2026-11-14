<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use Exception;
use ModulesGarden\TSSGGSModule\App\Models\Request;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Service;
use ModulesGarden\TSSGGSModule\Packages\Logs\Support\Facades\Logger;
use WHMCS\Database\Capsule;

/**
 * Certificate details sync shared by the SSLCertificates cron (services reported by /services/updates)
 * and the admin "Sync Certificate" button (single service, without the updates request).
 */
class CertificateSyncHelpers
{
    /**
     * Services which can be synchronized: active, module TSSGGSModule, order submitted to the API
     */
    public static function getCertificatesQuery()
    {
        // certificates kept as a record of the legacy module are not synced (App\Libs\RecordOnly)
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
            throw new Exception('The gateway returned no status for order ' . $certificate['remoteid']);
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
                Logger::error('Get Certificate Files Error:' . $exception->getMessage(), \ModulesGarden\TSSGGSModule\App\Libs\LogContext::exception($exception, ['service' => $certificate['id']]));
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

        if($orderData['order']['status'] == 'active') {
            // the order end (never the certificate end) - certificates within the order are reissued
            $nextDueDate = SslProductHelpers::getServiceDueDate($orderData, $orderFiles);
            if($nextDueDate) {
                Service::where('id', $certificate['id'])->update([
                    'nextinvoicedate' => $nextDueDate,
                    'nextduedate' => $nextDueDate,
                ]);
            }
        }

        if($orderData['order']['status'] == 'cancelled' || $orderData['order']['status'] == 'rejected')
        {
            Service::where('id', $certificate['id'])->update([
                'domainstatus' => 'Cancelled'
            ]);
        }
        elseif($orderData['order']['status'] == 'expired' || $orderData['order']['status'] == 'filed')
        {
            // an expired (or one-time, filed) certificate is terminated with a termination date, not cancelled; services
            // that already ended (Terminated / Cancelled / Fraud) are left as they are
            Service::where('id', $certificate['id'])->whereIn('domainstatus', ['Pending', 'Active', 'Suspended'])->update([
                'domainstatus'     => 'Terminated',
                'termination_date' => date('Y-m-d'),
            ]);
        }

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
