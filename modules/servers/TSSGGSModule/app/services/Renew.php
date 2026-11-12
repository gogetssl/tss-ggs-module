<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\services;

use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Service;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\SSL;
use ModulesGarden\TSSGGSModule\App\Libs\AcmeHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\AutoInstallHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\CertificateService;
use ModulesGarden\TSSGGSModule\App\Libs\SSLTrustCenterApi;
use ModulesGarden\TSSGGSModule\App\Models\Request;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\ProductRepository;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\InvoiceItem;

class Renew
{
    /**
     * WHMCS shows what is returned here: anything but "success" is a failed module action, so a vendor error is
     * returned (and logged) instead of leaving the service looking renewed
     */
    public function run($params)
    {
        try
        {
            return $this->renew($params);
        }
        catch(\Exception $e)
        {
            \logActivity('TSSGGS: Renewal of service #' . (int)$params['serviceid'] . ' failed: ' . $e->getMessage());

            return 'Renewal failed: ' . $e->getMessage();
        }
    }

    protected function renew($params)
    {
        $serviceId = $params['serviceid'];

        $service = Service::find($serviceId);
        if($service && AcmeHelpers::isAcmeWhmcsProduct($service->packageid)) {
            return 'success';
        }

        // S/MIME: a renewal is a new certificate order - the service goes back to Awaiting Configuration and the client
        // gets the configuration link (the current certificate stays valid until it expires)
        $gatewayType = $service ? \ModulesGarden\TSSGGSModule\App\Libs\GatewayOrderService::typeOfProduct($service->packageid) : null;
        if($gatewayType) {
            if($gatewayType::resetForRenewal($serviceId)) {
                $sslOrder = SSL::getByServiceId($serviceId);
                \ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates::sendEmail($gatewayType::configurationTemplate(), $serviceId, [
                    'ssl_configuration_link' => (new \ModulesGarden\Servers\TSSGGSModule\app\repository\whmcs\Config())->getConfigureSSLUrl($sslOrder ? $sslOrder->id : 0, $serviceId),
                ]);
            }

            return 'success';
        }

        $userId = $params['userid'];
        $pid = $params['pid'];
        $billingCycle = $params['model']->billingcycle;
        $paymentMethod = $params['model']->paymentmethod;
        $configOptions = $params['configoptions'];
        $domain = $params['domain'];

        $addonConfig = (new AddonModuleRepository())->getModuleConfiguration();
        $rate = $addonConfig['financeSettings']['rate'] ?: 1;
        $sslSettings = $addonConfig['sslSettings'];

        $renewOrderViaExistingOrder = $sslSettings['renewOrderViaExistingOrder'];
        $automaticProcessingOfRenewalOrders = $sslSettings['automaticProcessingOfRenewalOrders'];
        $configProduct = (new ProductRepository())->getProductConfiguration($pid);

        $provider = strtolower($configProduct['provider']);
        $credentials = $addonConfig['credentials'][$provider];

        if($provider == 'tss' && $credentials['OperationMode'] == 'sandbox')
        {
            $credentials['PartnerCode'] = $credentials['TestPartnerCode'];
            $credentials['AuthToken'] =  $credentials['TestAuthToken'];
        }

        $api = new SSLTrustCenterApi($configProduct['provider'], $credentials['PartnerCode'], $credentials['AuthToken'], $configProduct['category']);

        if($renewOrderViaExistingOrder)
        {
            $sslOrder = SSL::getByServiceId($serviceId);
            $request = $api->getOrder($sslOrder->remoteid, $serviceId);

            $requestRenew = $request['orderData'];

            $now = time();
            $nextduedate = strtotime($request['orderFiles']['validity']['end']);
            $datediff = $nextduedate - $now;
            $days = round($datediff / (60 * 60 * 24));

            $requestRenew['renewal']['prior_order_id'] = $requestRenew['renewal']['prior_certificate_id'] = $sslOrder->remoteid;
            $requestRenew['renewal']['remaining_days'] = CertificateService::renewalDays($serviceId, $days > 0 ? $days + 1 : 0);

            $newOrder  = $api->renewOrder($serviceId, $requestRenew);
            $newItemId = SSLTrustCenterApi::getItemId($newOrder);

            SSL::where('serviceid', $serviceId)->update(['remoteid' => $newItemId]);

            $invoiceId = 0;
            $item = InvoiceItem::where('relid', $serviceId)->where('type', 'Hosting')->orderBy('id','desc')->first();
            if(isset($item->invoiceid) && !empty($item->invoiceid))
            {
                $invoiceId = $item->invoiceid;
            }

            $apiPrice = $newOrder['order']['total_amount'];

            Request::where('serviceid', $serviceId)->where('name', 'renewOrder')->update([
                'invoiceid' => $invoiceId,
                'api_price' => $apiPrice,
                'whmcs_price' => $params['model']->amount,
                'rate' => $rate,
                'diff_price' => $params['model']->amount - $apiPrice,
                'status' => 'pending'
            ]);

            $orderData = $api->getOrder($newItemId, $serviceId);

            $orderFiles = [];
            try {
                $orderFiles = $api->getCertificateFiles($newItemId);
            } catch (\Exception $exception) {}

            $request = ['orderData' => $orderData, 'orderFiles' => $orderFiles];

            // keep the issued certificate: it stays valid (and downloadable) while the renewal waits for validation
            \ModulesGarden\TSSGGSModule\App\Libs\CertificateService::keepPreviousCertificate($serviceId);

            // (insert() ignores the where() conditions: the row needs its own serviceid and name)
            Request::where('serviceid', $serviceId)->where('name', 'certificate')->delete();
            Request::insert([
                'serviceid' => $serviceId,
                'name' => 'certificate',
                'request' => \encrypt(json_encode($request)),
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // AutoInstall: install the renewed certificate (same CSR / private key) + "Renewal Started"; otherwise "Autorenewed" email
            try {
                AutoInstallHelpers::afterRenewal($serviceId, $serviceId, $newItemId);
            } catch (\Exception $exception) {}
        }

        if(!$renewOrderViaExistingOrder)
        {
            $resultsOrder = localAPI('AddOrder', [
                'clientid' => $userId,
                'pid' => [$pid],
                'qty' => [1],
                'billingcycle' => [$billingCycle],
                'configoptions' => [base64_encode(serialize($configOptions))],
                'paymentmethod' => $paymentMethod,
                'noinvoice' => true,
                'noemail' => true
            ]);

            if($resultsOrder['result'] == 'success')
            {
                localAPI('AcceptOrder', [
                    'orderid' => $resultsOrder['orderid'],
                    'autosetup' => true,
                    'sendemail' => $automaticProcessingOfRenewalOrders ? false : true,
                ]);

                $newServices = explode(',', $resultsOrder['serviceids']);
                $newServiceId = reset($newServices);
                if(!$newServiceId) {
                    return 'The order could not be created. Please check logs.';
                }
                Service::where('id', $newServiceId)->update(['domain' => $domain]);
            }
            else
            {
                return 'The renewal order could not be created: ' . ($resultsOrder['message'] ?? 'unknown error');
            }

            if($automaticProcessingOfRenewalOrders)
            {
                $sslOrder = SSL::getByServiceId($serviceId);
                $request = $api->getOrder($sslOrder->remoteid, $serviceId);

                $requestRenew = $request['orderData'];

                $now = time();
                $nextduedate = strtotime($request['orderFiles']['validity']['end']);
                $datediff = $nextduedate - $now;
                $days = round($datediff / (60 * 60 * 24));
                $requestRenew['renewal']['prior_order_id'] = $requestRenew['renewal']['prior_certificate_id'] = $sslOrder->remoteid;
                $requestRenew['renewal']['remaining_days'] = CertificateService::renewalDays($serviceId, $days > 0 ? $days + 1 : 0);

                $newOrder = $api->renewOrder($newServiceId, $requestRenew);
                $newItemId = SSLTrustCenterApi::getItemId($newOrder);
                $newService = Service::where('id', $newServiceId)->first();

                $invoiceId = 0;
                $item = InvoiceItem::where('relid', $newServiceId)->where('type', 'Hosting')->orderBy('id','desc')->first();
                if(isset($item->invoiceid) && !empty($item->invoiceid))
                {
                    $invoiceId = $item->invoiceid;
                }

                $apiPrice = $newOrder['order']['total_amount'];

                Request::where('serviceid', $newServiceId)->where('name', 'renewOrder')->update([
                    'invoiceid' => $invoiceId,
                    'api_price' => $apiPrice,
                    'whmcs_price' => $newService->amount,
                    'rate' => $rate,
                    'diff_price' => $newService->amount - $apiPrice,
                    'status' => 'pending'
                ]);

                $orderData = $api->getOrder($newItemId, $newServiceId);

                $orderFiles = [];
                try {
                    $orderFiles = $api->getCertificateFiles($newItemId);
                } catch (\Exception $exception) {}

                $request = ['orderData' => $orderData, 'orderFiles' => $orderFiles];
                Request::insert([
                    'serviceid' => $newServiceId,
                    'name' => 'certificate',
                    'request' => \encrypt(json_encode($request)),
                    'status' => 'pending',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                SSL::where('serviceid', $newServiceId)->update([
                    'remoteid' => $newItemId,
                    'completiondate' => date('Y-m-d H:i:s'),
                    'status' => 'Configuration Submitted'
                ]);

                // AutoInstall: install the renewed certificate (same CSR / private key) + "Renewal Started"; otherwise "Autorenewed" email
                try {
                    AutoInstallHelpers::afterRenewal($serviceId, $newServiceId, $newItemId);
                } catch (\Exception $exception) {}
            }
        }

        return 'success';
    }

}