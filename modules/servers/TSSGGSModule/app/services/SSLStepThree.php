<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\services;

use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Service;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\SSL;
use ModulesGarden\TSSGGSModule\App\Libs\CertificateService;
use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\Libs\ProductSettings;
use ModulesGarden\TSSGGSModule\App\Libs\SanUpgrade;
use ModulesGarden\TSSGGSModule\App\Libs\SSLTrustCenterApi;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\ProductRepository;
use ModulesGarden\TSSGGSModule\App\Models\Request;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\InvoiceItem;

class SSLStepThree
{

    public function run($params)
    {
        try {

            $serviceDetails = $params['model']->toArray();
            // a product addon's certificate: its term follows the addon's billing cycle (the service itself is free)
            $serviceDetails['billingcycle'] = \ModulesGarden\TSSGGSModule\App\Libs\AddonCertificates::billingCycleOf((int)$params['serviceid']) ?? $serviceDetails['billingcycle'];

            $billingCycle = 12;
            switch ($serviceDetails['billingcycle']) {
                case "Free Account":
                    $billingCycle = 12;
                    break;
                case "One Time":
                    $billingCycle = 12;
                    break;
                case "Monthly":
                    $billingCycle = 12;
                    break;
                case "Quarterly":
                    $billingCycle = 12;
                    break;
                case "Semi-Annually":
                    $billingCycle = 12;
                    break;
                case "Annually":
                    $billingCycle = 12;
                    break;
                case "Biennially":
                    $billingCycle = 24;
                    break;
                case "Triennially":
                    $billingCycle = 36;
                    break;
            }

            // Term set on the Products page wins over the billing cycle (e.g. a monthly billed 1-year certificate)
            $productMonths = (int)ProductSettings::get($params['packageid'])['months'];
            if($productMonths > 0)
            {
                $billingCycle = $productMonths;
            }

            $configProduct = (new ProductRepository())->getProductConfiguration($params['packageid']);
            $addonConfig = (new AddonModuleRepository())->getModuleConfiguration();

            $rate = $addonConfig['financeSettings']['rate'] ?: 1;

            $api = SSLTrustCenterApi::forVendor($configProduct['provider'], $configProduct['category']);
            $decode = openssl_csr_get_subject($params['csr']);
            // the order's main name: the CSR's common name, or - for a CSR without one (IP certificates,
            // App\Libs\IpCertificate) - the name the configuration gave
            $commonName = !empty($params['common_name']) ? (string)$params['common_name'] : (string)($decode['CN'] ?? '');
            $mainMethod = $params['method'][$commonName] == 'email' ? $params['approver'][$commonName] : $params['method'][$commonName];

            SanUpgrade::assertWithinLimits($params['serviceid'], $commonName, array_keys($params['method']), SanUpgrade::paidQuantities($params));
            // *. only on a wildcard product, or a FLEX product with the Wildcard choice paid
            \ModulesGarden\TSSGGSModule\App\Libs\MainDomain::assertCommonName((int)$params['serviceid'], $commonName);

            $san = [];
            // a CSR without common name (IP certificates): every name is a SAN of the order, the main name too - the
            // vendor refuses the order without one ("minimum numbers of SAN is 1")
            $allNamesAreSans = !empty($params['common_name']) && empty($decode['CN']);
            foreach ($params['method'] as $domain_san => $method_san)
            {
                if($domain_san == $commonName && !$allNamesAreSans) continue;
                $methodSan = $method_san == 'email' ? $params['approver'][$domain_san] : $method_san;
                $san[] = ['name' => $domain_san, 'validation_method' => $methodSan];
            }

            $orderParams = [
                'product' => [
                    'id' => $configProduct['product_id'],
                    'term' => $billingCycle,
                ],
                'csr' => trim($params['csr']),
                'common_name' => [
                    'name' => $commonName,
                    'validation_method' => $mainMethod,
                ],
                'contacts' => [
                    'administrator' => $this->prepareContactAdminTech($params),
                    'technical' => $this->prepareContactAdminTech($params)
                ]
            ];

            if(!empty($san))
            {
                $orderParams['alternative_names'] = $san;
            }

            // chosen by the admin (settings), the client is not asked
            $webServer = trim((string)\ModulesGarden\TSSGGSModule\App\Libs\GeneralSettings::get('defaultWebServer'));
            if($webServer !== '')
            {
                $orderParams['webserver_type'] = $webServer;
            }

            if(in_array($configProduct['validation'], ['OV','EV']))
            {
                $orderParams['contacts']['organization'] = $this->prepareContactOrganization($params);
            }

            // a paid renewal that waited for these details: placed as the renewal of the previous order, so the
            // vendor carries its days over (App\Libs\RenewalPolicy)
            $renewal = \ModulesGarden\TSSGGSModule\App\Libs\RenewalPolicy::pendingRenewal((int)$params['serviceid']);
            if($renewal)
            {
                $orderParams['renewal'] = $renewal;
            }

            // a renewal on the same service is stored as a renewOrder row, as Renew stores its own: the rows of the
            // earlier orders keep their invoice and prices, and the next automatic renewal reads this one
            $pendingRow   = Request::where('serviceid', $params['serviceid'])->where('name', 'renewalPending')->value('request');
            $pendingData  = $pendingRow ? (json_decode((string)$pendingRow, true) ?: []) : [];
            $sameRenewal  = $renewal && (int)($pendingData['prior_service'] ?? 0) === (int)$params['serviceid'];

            // a certificate bought at payment (IncompleteOrder): its details complete that order with the token
            $incomplete = \ModulesGarden\TSSGGSModule\App\Libs\IncompleteOrder::pending($params['serviceid']);
            $place      = function(array $orderParams) use ($api, $params, $incomplete, $sameRenewal) {
                if($incomplete)
                {
                    return $api->completeOrder($incomplete['token'], $orderParams, $params['serviceid']);
                }
                return $sameRenewal
                    ? $api->renewOrder($params['serviceid'], $orderParams)
                    : $api->addOrder($orderParams, $params['serviceid']);
            };

            try
            {
                $newOrder = $place($orderParams);
            }
            catch(\Exception $renewalRefused)
            {
                // the token was used meanwhile (a plugin submitted the details): the service follows that order
                if($incomplete && \ModulesGarden\TSSGGSModule\App\Libs\IncompleteOrder::adoptIfCompleted($params['serviceid']))
                {
                    return ['adopted' => true, 'error' => \ModulesGarden\TSSGGSModule\App\Libs\ClientText::get('orderCompletedElsewhere', 'The details of this certificate were already submitted with its token (for example by a hosting panel plugin). Reload the page to follow the order.')];
                }
                if(!$renewal)
                {
                    throw $renewalRefused;
                }
                // the vendor renews an order only inside its renewal window: the paid renewal is then ordered as a new
                // certificate with these details (only the carried-over days are lost); a refusal of the details
                // themselves comes back from this second attempt and is shown to the client as before
                unset($orderParams['renewal']);
                $newOrder = $place($orderParams);
                \ModulesGarden\TSSGGSModule\App\Libs\CertificateService::event($params['serviceid'], 'info', '[Renewal] The vendor did not take it as a renewal of the previous order (' . $renewalRefused->getMessage() . '): ordered as a new certificate');
            }
            if($renewal)
            {
                \ModulesGarden\TSSGGSModule\App\Libs\RenewalPolicy::renewalPlaced((int)$params['serviceid']);
            }
            $itemId   = SSLTrustCenterApi::getItemId($newOrder, $incomplete ? $incomplete['item_id'] : null);
            if($incomplete)
            {
                \ModulesGarden\TSSGGSModule\App\Libs\IncompleteOrder::markCompleted($params['serviceid']);
            }
            SSL::where('serviceid', $params['serviceid'])->update(['remoteid' => $itemId]);
            Service::where('id', $params['serviceid'])->update(['domain' => $commonName]);

            // this order's row: the one bought at payment (addOrder, or renewOrder for a renewal on this service) or
            // the one placed just now
            $purchaseRow = $incomplete ? Request::currentOrder($params['serviceid'])
                : Request::where('serviceid', $params['serviceid'])->where('name', $sameRenewal ? 'renewOrder' : 'addOrder')->orderBy('id', 'desc')->first();

            // a completed order was paid at the purchase: its price is on its row already
            $apiPrice = $incomplete ? (float)($purchaseRow->api_price ?? 0) : $newOrder['order']['total_amount'];

            // the invoice of this order (a renewal has its own), not the first one of the service
            $invoiceId = 0;
            $item = InvoiceItem::where('relid', $params['serviceid'])->where('type', 'Hosting')->orderBy('id', 'desc')->first();
            if(isset($item->invoiceid) && !empty($item->invoiceid))
            {
                $invoiceId = $item->invoiceid;
            }
            // a product addon's certificate: the client pays the addon, the service itself is free
            $sellingPrice = (float)$params['model']->amount;
            $addonBilling = \ModulesGarden\TSSGGSModule\App\Libs\AddonCertificates::billing((int)$params['serviceid']);
            if($addonBilling)
            {
                $sellingPrice = $addonBilling['amount'];
                $invoiceId    = $addonBilling['invoiceid'] ?: $invoiceId;
            }

            Request::where('id', $purchaseRow ? (int)$purchaseRow->id : 0)->update([
                'invoiceid' => $invoiceId,
                'api_price' => $apiPrice,
                'whmcs_price' => $sellingPrice,
                'rate' => $rate,
                'diff_price' => $sellingPrice - $apiPrice,
                'status' => $newOrder['order']['status']
            ]);

            // the order is placed (and paid); if the gateway can not show it yet, the answer of the order call stands in
            // until the order status sync reads it - the client must not see an error for an order that went through
            $orderData = $api->getOrderOr($itemId, $params['serviceid'], (array)$newOrder);

            $orderFiles = [];
            $request = ['orderData' => $orderData, 'orderFiles' => $orderFiles];

            Request::updateOrInsert(
                ['serviceid' => $params['serviceid'], 'name' => 'certificate'],
                [
                    'request' => \encrypt(json_encode($request)),
                    'status' => $newOrder['order']['status'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            );

            // (the order status sync picks the order up when the vendor reports it; the client's session id is
            // no longer stored for a task runner that never ran)

            // SANs paid with this order - an upgrade of the SAN options orders only the difference (see SanUpgrade)
            SanUpgrade::rememberPurchased($params['serviceid'], SanUpgrade::paidQuantities($params));

            CertificateService::event($params['serviceid'], 'success', '[Order] SSL order ' . ($incomplete ? 'completed' : 'placed') . ' for domain: ' . $commonName
                . ' (order #' . ($newOrder['order']['id'] ?? ($incomplete['order_id'] ?? '')) . ', ' . (count($san) + 1) . ' domain(s))', $params['userid']);

            // OV / EV: tell the client the certificate authority now verifies the organization
            \ModulesGarden\TSSGGSModule\App\Libs\OrganizationValidation::notify((int)$params['serviceid'], $itemId, (bool)$renewal);

            // Integrator hook; SSLCenter_StepThree keeps hooks written for the GoGetSSL module working after the migration
            $hookParams = [
                'serviceid'     => $params['serviceid'],
                'userid'        => $params['userid'],
                'api_ssl_order' => $newOrder,
                'order_details' => $orderParams,
            ];
            run_hook('TSSGGSModule_OrderPlaced', $hookParams);
            run_hook('SSLCenter_StepThree', $hookParams);

        } catch (\Exception $e) {

            return ['error' => $e->getMessage()];

        }
    }

    public function prepareContactAdminTech($params)
    {
        $phone = str_replace([' ', '-', '(', ')','.'], '', $params['phonenumber']);
        return [
            'first_name' => $params['firstname'],
            'last_name' => $params['lastname'],
            'title' => $params['jobtitle'],
            'email' => $params['email'],
            'phone' => $phone
        ];
    }

    public function prepareContactOrganization($params)
    {
        $phone = str_replace([' ', '-', '(', ')','.'], '', $params['phonenumber']);
        return [
            'name' => $params['firstname'].' '.$params['lastname'],
            'address_line_1' => $params['address1'],
            'address_line_2' => '',
            'city' => $params['city'],
            'region' => $params['state'],
            'country' => $params['country'],
            'postal_code' => $params['postcode'],
            'phone' => $phone
        ];
    }
}