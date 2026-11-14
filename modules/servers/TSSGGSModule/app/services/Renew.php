<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\services;

use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Service;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\SSL;
use ModulesGarden\TSSGGSModule\App\Libs\AcmeHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\AutoInstallHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\CertificateService;
use ModulesGarden\TSSGGSModule\App\Libs\RenewalPolicy;
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
        // subscriptions are renewed by the vendor (SubscriptionRenewals bills them): nothing to order
        if($service && \ModulesGarden\TSSGGSModule\App\Libs\SubscriptionBilling::isSubscriptionProduct($service->packageid)) {
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

        $addonConfig   = (new AddonModuleRepository())->getModuleConfiguration();
        $rate          = $addonConfig['financeSettings']['rate'] ?: 1;
        $configProduct = (new ProductRepository())->getProductConfiguration($params['pid']);
        $api           = SSLTrustCenterApi::forVendor($configProduct['provider'], $configProduct['category']);

        // two independent choices (App\Libs\RenewalPolicy): which WHMCS service carries the renewal, and whether it is
        // ordered with the previous details at once or the client submits them again
        $serviceId   = (int)$serviceId;
        $sameService = RenewalPolicy::sameService();
        $target      = $sameService ? $serviceId : $this->renewalService($params);
        $amount      = $sameService ? (float)$params['model']->amount : (float)Service::where('id', $target)->value('amount');

        if(RenewalPolicy::reuseDetails())
        {
            try
            {
                // the "Certificate Autorenewed" e-mail (or AutoInstall's "Renewal Started") goes out from there
                $this->orderRenewal($api, $serviceId, $target, $amount, $rate);
                $this->retire($serviceId, $target);

                return 'success';
            }
            catch(\Throwable $e)
            {
                // the vendor refused the previous details (a reused key, an organisation to validate again ...):
                // the renewal is paid, so the client is asked for new details instead of the renewal failing
                CertificateService::event($serviceId, 'error', '[Renewal] Not accepted with the previous details, the client is asked for new ones: ' . $e->getMessage());
            }
        }

        $this->askForDetails($serviceId, $target);
        $this->retire($serviceId, $target);

        return 'success';
    }

    /**
     * Renewal on a new order: the new WHMCS service that carries it (set up at once, waiting for its certificate).
     * WHMCS sends nothing here - the client gets the module's own e-mail.
     */
    protected function renewalService($params): int
    {
        $result = localAPI('AddOrder', [
            'clientid'      => $params['userid'],
            'pid'           => [$params['pid']],
            'qty'           => [1],
            'billingcycle'  => [$params['model']->billingcycle],
            'configoptions' => [base64_encode(serialize($params['configoptions']))],
            'paymentmethod' => $params['model']->paymentmethod,
            'noinvoice'     => true,
            'noemail'       => true,
        ]);
        if(($result['result'] ?? '') !== 'success')
        {
            throw new \Exception('The renewal order could not be created: ' . ($result['message'] ?? 'unknown error'));
        }

        localAPI('AcceptOrder', ['orderid' => $result['orderid'], 'autosetup' => true, 'sendemail' => false]);

        $ids = array_filter(explode(',', (string)($result['serviceids'] ?? '')));
        $id  = (int)reset($ids);
        if(!$id)
        {
            throw new \Exception('The renewal order could not be created. Please check logs.');
        }
        Service::where('id', $id)->update(['domain' => $params['domain']]);

        return $id;
    }

    /**
     * The renewal waits for the client's details: the service that carries it goes back to Awaiting Configuration
     * (the renewing service keeps its current certificate, downloadable until it expires), remembers which order it
     * renews - so the order the client submits is placed as that order's renewal and its days are carried over -
     * and the client gets the "Renewal Details Required" e-mail.
     */
    protected function askForDetails(int $serviceId, int $target): void
    {
        $sslOrder  = SSL::getByServiceId($serviceId);
        $priorItem = $sslOrder ? (string)$sslOrder->remoteid : '';
        $priorEnd  = (string)(CertificateService::stored($serviceId)['orderFiles']['validity']['end'] ?? '');

        if($serviceId === $target)
        {
            CertificateService::keepPreviousCertificate($serviceId);
            SSL::where('serviceid', $serviceId)->update(['status' => 'Awaiting Configuration', 'remoteid' => '']);
        }
        RenewalPolicy::awaitDetails($target, $serviceId, $priorItem, $priorEnd);

        $targetOrder = SSL::getByServiceId($target);
        \ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates::sendEmail(\ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates::RENEWAL_DETAILS_TEMPLATE, $target, [
            'ssl_configuration_link' => (new \ModulesGarden\Servers\TSSGGSModule\app\repository\whmcs\Config())->getConfigureSSLUrl($targetOrder ? $targetOrder->id : 0, $target),
        ]);
        CertificateService::event($target, 'info', '[Renewal] Paid: waiting for the client to submit the renewal details');
    }

    /**
     * Renewal on a new order: the renewed service is billed no more - the new one carries the next renewals. It stays
     * open, its certificate downloadable, until its order ends; the renewal cron then marks it Completed.
     */
    protected function retire(int $serviceId, int $target): void
    {
        if($serviceId === $target)
        {
            return;
        }
        Service::where('id', $serviceId)->update(['billingcycle' => 'One Time']);
        \ModulesGarden\TSSGGSModule\App\Models\Request::updateOrInsert(
            ['serviceid' => $serviceId, 'name' => 'renewedTo'],
            ['request' => (string)$target, 'status' => 'renewed', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]
        );
        CertificateService::event($serviceId, 'info', '[Renewal] Renewed as service #' . $target . ': this service is billed no more and closes when its order ends');
        CertificateService::event($target, 'info', '[Renewal] Renewal of service #' . $serviceId);
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

        try
        {
            $newOrder = $api->renewOrder($targetServiceId, $requestRenew);
        }
        catch(\Throwable $e)
        {
            // the vendor renews an order only inside its renewal window ("This order is not up for renewal" - a renewal
            // paid long before the end): the paid renewal is then ordered as a new certificate with the same details;
            // only the carried-over days are lost. When that is refused too, the caller asks the client for new details.
            unset($requestRenew['renewal']);
            $newOrder = $api->renewOrder($targetServiceId, $requestRenew);
            CertificateService::event($serviceId, 'info', '[Renewal] The vendor did not take it as a renewal of the previous order (' . $e->getMessage() . '): ordered as a new certificate with the same details');
        }
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