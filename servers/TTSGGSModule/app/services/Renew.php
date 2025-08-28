<?php

namespace ModulesGarden\Servers\TTSGGSModule\app\services;

use ModulesGarden\Servers\TTSGGSModule\app\models\whmcs\Service;
use ModulesGarden\Servers\TTSGGSModule\app\models\whmcs\SSL;
use ModulesGarden\TTSGGSModule\App\Libs\SSLTrustCenterApi;
use ModulesGarden\TTSGGSModule\App\Models\Request;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\ProductRepository;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\InvoiceItem;

class Renew
{
    public function run($params)
    {
        $serviceId = $params['serviceid'];
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

            $requestRenew['renewal']['prior_order_id'] = $sslOrder->remoteid;
            $requestRenew['renewal']['remaining_days'] = $days > 0 ? $days+1 : 0;

            $newOrder = $api->renewOrder($serviceId, $requestRenew);

            SSL::where('serviceid', $serviceId)->update(['remoteid' => $newOrder['order']['id']]);

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

            $orderData = $api->getOrder($newOrder['order']['id'], $serviceId);

            $orderFiles = [];
            try {
                $orderFiles = $api->getCertificateFiles($newOrder['order']['id']);
            } catch (\Exception $exception) {}

            $request = ['orderData' => $orderData, 'orderFiles' => $orderFiles];

            Request::where('serviceid', $serviceId)->where('name', 'certificate')->insert([
                'request' => \encrypt(json_encode($request)),
                'status' => 'pending',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
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

            if($automaticProcessingOfRenewalOrders)
            {
                $sslOrder = SSL::getByServiceId($serviceId);
                $request = $api->getOrder($sslOrder->remoteid, $serviceId);

                $requestRenew = $request['orderData'];

                $now = time();
                $nextduedate = strtotime($request['orderFiles']['validity']['end']);
                $datediff = $nextduedate - $now;
                $days = round($datediff / (60 * 60 * 24));
                $requestRenew['renewal']['prior_order_id'] = $sslOrder->remoteid;
                $requestRenew['renewal']['remaining_days'] = $days > 0 ? $days+1 : 0;

                $newOrder = $api->renewOrder($newServiceId, $requestRenew);
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

                $orderData = $api->getOrder($newOrder['order']['id'], $newServiceId);

                $orderFiles = [];
                try {
                    $orderFiles = $api->getCertificateFiles($newOrder['order']['id']);
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
                    'remoteid' => $newOrder['order']['id'],
                    'completiondate' => date('Y-m-d H:i:s'),
                    'status' => 'Configuration Submitted'
                ]);
            }
        }

        return 'success';
    }

}