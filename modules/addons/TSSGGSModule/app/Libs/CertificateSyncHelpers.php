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
        return Service::select(['tblhosting.*', 'tblsslorders.remoteid', 'tblproducts.configoption1', 'tblproducts.configoption9'])
            ->join('tblsslorders', 'tblsslorders.serviceid', '=', 'tblhosting.id')
            ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
            ->where('tblproducts.servertype', 'TSSGGSModule')
            ->where('tblhosting.domainstatus', 'Active')
            ->where('tblsslorders.status', 'Configuration Submitted');
    }

    /**
     * @param array $certificate row from getCertificatesQuery()
     * @param array $addonConfig AddonModuleRepository::getModuleConfiguration()
     * @return string remote order status
     * @throws Exception
     */
    public static function syncCertificate(array $certificate, array $addonConfig)
    {
        $provider    = strtolower($certificate['configoption1']);
        $credentials = $addonConfig['credentials'][$provider];

        if($provider == 'tss' && $credentials['OperationMode'] == 'sandbox')
        {
            $credentials['PartnerCode'] = $credentials['TestPartnerCode'];
            $credentials['AuthToken']   = $credentials['TestAuthToken'];
        }

        $api       = new SSLTrustCenterApi($certificate['configoption1'], $credentials['PartnerCode'], $credentials['AuthToken'], $certificate['configoption9']);
        $orderData = $api->getOrder($certificate['remoteid'], $certificate['id']);

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
                Logger::error('Get Certificate Files Error:' . $exception->getMessage(), ['service' => $certificate['id']]);
            }
        }

        Request::where('serviceid', $certificate['id'])->where('name', 'addOrder')->update([
            'status' => $orderData['order']['status']
        ]);

        $request = ['orderData' => $orderData, 'orderFiles' => $orderFiles];

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
            // Subscription end (falls back to the certificate end) - certificates within the subscription are reissued
            $nextDueDate = SslProductHelpers::getServiceDueDate($orderData, $orderFiles);
            if($nextDueDate) {
                Service::where('id', $certificate['id'])->update([
                    'nextinvoicedate' => $nextDueDate,
                    'nextduedate' => $nextDueDate,
                ]);
            }
        }

        if($orderData['order']['status'] == 'cancelled' || $orderData['order']['status'] == 'expired' || $orderData['order']['status'] == 'filed' || $orderData['order']['status'] == 'rejected')
        {
            Service::where('id', $certificate['id'])->update([
                'domainstatus' => 'Cancelled'
            ]);
        }

        Logger::info("The certificate details has been updated", ['service' => $certificate['id']]);

        return $orderData['order']['status'];
    }
}
