<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\services;

use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Service;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\SSL;
use ModulesGarden\TSSGGSModule\App\Libs\CertificateService;
use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\Libs\ProductSettings;
use ModulesGarden\TSSGGSModule\App\Libs\SanUpgrade;
use ModulesGarden\TSSGGSModule\App\Libs\SSLTrustCenterApi;
use ModulesGarden\TSSGGSModule\App\Models\DemonTask;
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
            $mainMethod = $params['method'][$decode['CN']] == 'email' ? $params['approver'][$decode['CN']] : $params['method'][$decode['CN']];

            SanUpgrade::assertWithinLimits($params['serviceid'], $decode['CN'], array_keys($params['method']), SanUpgrade::paidQuantities($params));

            $san = [];
            foreach ($params['method'] as $domain_san => $method_san)
            {
                if($domain_san == $decode['CN']) continue;
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
                    'name' => $decode['CN'],
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

            $newOrder = $api->addOrder($orderParams, $params['serviceid']);
            if($renewal)
            {
                \ModulesGarden\TSSGGSModule\App\Libs\RenewalPolicy::renewalPlaced((int)$params['serviceid']);
            }
            $itemId   = SSLTrustCenterApi::getItemId($newOrder);
            SSL::where('serviceid', $params['serviceid'])->update(['remoteid' => $itemId]);
            Service::where('id', $params['serviceid'])->update(['domain' => $decode['CN']]);

            $apiPrice = $newOrder['order']['total_amount'];

            $invoiceId = 0;
            $item = InvoiceItem::where('relid', $params['serviceid'])->where('type', 'Hosting')->first();
            if(isset($item->invoiceid) && !empty($item->invoiceid))
            {
                $invoiceId = $item->invoiceid;
            }

            Request::where('serviceid', $params['serviceid'])->where('name', 'addOrder')->update([
                'invoiceid' => $invoiceId,
                'api_price' => $apiPrice,
                'whmcs_price' => $params['model']->amount,
                'rate' => $rate,
                'diff_price' => $params['model']->amount - $apiPrice,
                'status' => $newOrder['order']['status']
            ]);

            $orderData = $api->getOrder($itemId, $params['serviceid']);

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

            if($newOrder['order']['status'] == 'processing')
            {
                DemonTask::insert([
                    'session_id' => session_id(),
                    'service_id' => $params['serviceid'],
                    'status' => 'waiting',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            // SANs paid with this order - an upgrade of the SAN options orders only the difference (see SanUpgrade)
            SanUpgrade::rememberPurchased($params['serviceid'], SanUpgrade::paidQuantities($params));

            CertificateService::event($params['serviceid'], 'success', '[Order] SSL order placed for domain: ' . $decode['CN']
                . ' (order #' . $newOrder['order']['id'] . ', ' . (count($san) + 1) . ' domain(s))', $params['userid']);

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