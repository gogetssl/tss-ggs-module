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
        catch(\Throwable $e)
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
            // "ordered": placed with the previous details; "form": the client gets the order details e-mail
            if($gatewayType::renewal($serviceId) === 'form') {
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

        $api = SSLTrustCenterApi::forVendor($configProduct['provider'], $configProduct['category']);

        if($renewOrderViaExistingOrder)
        {
            $this->orderRenewal($api, (int)$serviceId, (int)$serviceId, (float)$params['model']->amount, $rate);
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
                $newService = Service::where('id', $newServiceId)->first();
                $this->orderRenewal($api, (int)$serviceId, (int)$newServiceId, (float)$newService->amount, $rate);
            }
        }

        return 'success';
    }

    /**
     * Orders the renewal of $serviceId's certificate at the vendor for $targetServiceId (the same service, or the new
     * service of a "renew via a new order" renewal) and stores it. Returns the new certificate (item) ID.
     */
    protected function orderRenewal($api, $serviceId, $targetServiceId, $amount, $rate)
    {
        $sslOrder = SSL::getByServiceId($serviceId);
        if(!$sslOrder || !$sslOrder->remoteid)
        {
            throw new \Exception('The service has no certificate order to renew');
        }
        $request      = $api->getOrder($sslOrder->remoteid, $serviceId);
        $requestRenew = $request['orderData'];

        // days left on the current certificate (its end date is in the stored certificate, the gateway order answer has none)
        $now  = time();
        $end  = strtotime((string)(CertificateService::stored($serviceId)['orderFiles']['validity']['end'] ?? '')) ?: $now;
        $days = round(($end - $now) / (60 * 60 * 24));

        $requestRenew['renewal']['prior_order_id'] = $requestRenew['renewal']['prior_certificate_id'] = $sslOrder->remoteid;
        $requestRenew['renewal']['remaining_days'] = CertificateService::renewalDays($serviceId, $days > 0 ? $days + 1 : 0);

        $newOrder  = $api->renewOrder($targetServiceId, $requestRenew);
        $newItemId = SSLTrustCenterApi::getItemId($newOrder);

        // from here the order exists at the vendor: what follows is stored on a best effort, a failure never reports the
        // renewal as failed (a second attempt would order a second certificate) - the certificates sync fills in the rest
        $sameService = (int)$serviceId === (int)$targetServiceId;
        SSL::where('serviceid', $targetServiceId)->update($sameService ? ['remoteid' => $newItemId] : [
            'remoteid'       => $newItemId,
            'completiondate' => date('Y-m-d H:i:s'),
            'status'         => 'Configuration Submitted',
        ]);

        try
        {
            if($sameService)
            {
                // keep the issued certificate: it stays valid (and downloadable) while the renewal waits for validation
                CertificateService::keepPreviousCertificate($serviceId);
            }
            $item     = InvoiceItem::where('relid', $targetServiceId)->where('type', 'Hosting')->orderBy('id', 'desc')->first();
            $apiPrice = $newOrder['order']['total_amount'] ?? 0;

            // this renewal's row only: the rows of earlier renewals keep their own invoice and prices
            Request::where('id', (int)Request::where('serviceid', $targetServiceId)->where('name', 'renewOrder')->max('id'))->update([
                'invoiceid'   => !empty($item->invoiceid) ? $item->invoiceid : 0,
                'api_price'   => $apiPrice,
                'whmcs_price' => $amount,
                'rate'        => $rate,
                'diff_price'  => $amount - $apiPrice,
                'status'      => 'pending',
            ]);

            $orderData  = $api->getOrder($newItemId, $targetServiceId);
            $orderFiles = [];
            try
            {
                $orderFiles = $api->getCertificateFiles($newItemId);
            }
            catch(\Throwable $exception)
            {
                // not issued yet
            }

            // (insert() ignores the where() conditions: the row needs its own serviceid and name)
            Request::where('serviceid', $targetServiceId)->where('name', 'certificate')->delete();
            Request::insert([
                'serviceid'  => $targetServiceId,
                'name'       => 'certificate',
                'request'    => \encrypt(json_encode(['orderData' => $orderData, 'orderFiles' => $orderFiles])),
                'status'     => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        catch(\Throwable $exception)
        {
            \logActivity('TSSGGS: Renewal of service #' . (int)$serviceId . ' was ordered (certificate ' . $newItemId . '), its details will be read by the next certificates sync: ' . $exception->getMessage());
        }

        // AutoInstall: install the renewed certificate (same CSR / private key) + "Renewal Started"; otherwise "Autorenewed" email
        try
        {
            AutoInstallHelpers::afterRenewal($serviceId, $targetServiceId, $newItemId);
        }
        catch(\Throwable $exception)
        {
            // the renewal itself is done
        }

        return $newItemId;
    }

}