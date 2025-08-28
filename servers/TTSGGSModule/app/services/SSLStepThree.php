<?php

namespace ModulesGarden\Servers\TTSGGSModule\app\services;

use ModulesGarden\Servers\TTSGGSModule\app\models\whmcs\Service;
use ModulesGarden\Servers\TTSGGSModule\app\models\whmcs\SSL;
use ModulesGarden\TTSGGSModule\App\Libs\Helpers;
use ModulesGarden\TTSGGSModule\App\Libs\SSLTrustCenterApi;
use ModulesGarden\TTSGGSModule\App\Models\DemonTask;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\ProductRepository;
use ModulesGarden\TTSGGSModule\App\Models\Request;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\InvoiceItem;

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

            $configProduct = (new ProductRepository())->getProductConfiguration($params['packageid']);
            $addonConfig = (new AddonModuleRepository())->getModuleConfiguration();

            $rate = $addonConfig['financeSettings']['rate'] ?: 1;

            $provider = strtolower($configProduct['provider']);
            $credentials = $addonConfig['credentials'][$provider];

            if($provider == 'tss' && $credentials['OperationMode'] == 'sandbox')
            {
                $credentials['PartnerCode'] = $credentials['TestPartnerCode'];
                $credentials['AuthToken'] =  $credentials['TestAuthToken'];
            }

            $api = new SSLTrustCenterApi($configProduct['provider'], $credentials['PartnerCode'], $credentials['AuthToken'], $configProduct['category']);
            $decode = openssl_csr_get_subject($params['csr']);
            $mainMethod = $params['method'][$decode['CN']] == 'email' ? $params['approver'][$decode['CN']] : $params['method'][$decode['CN']];

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

            if(in_array($configProduct['validation'], ['OV','EV']))
            {
                $orderParams['contacts']['organization'] = $this->prepareContactOrganization($params);
            }

            $newOrder = $api->addOrder($orderParams, $params['serviceid']);
            SSL::where('serviceid', $params['serviceid'])->update(['remoteid' => $newOrder['order']['id']]);
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

            $orderData = $api->getOrder($newOrder['order']['id'], $params['serviceid']);

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