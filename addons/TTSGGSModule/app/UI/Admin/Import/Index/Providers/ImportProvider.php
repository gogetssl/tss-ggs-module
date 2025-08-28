<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Import\Index\Providers;

use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Service;
use WHMCS\Database\Capsule;
use ModulesGarden\TTSGGSModule\App\Libs\Helpers;
use ModulesGarden\TTSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\WhmcsApi;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Client;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\PaymentGateway;
use ModulesGarden\TTSGGSModule\Packages\Logs\Support\Facades\Logger;
use ModulesGarden\TTSGGSModule\App\Models\Request as RequestModel;


class ImportProvider extends CrudProvider
{
    public function read()
    {
        $this->data = $this->formData;

        $this->availableValues['clientId']  = Helpers::getClientOptions();
        $this->availableValues['payMethod'] = Helpers::getPayMethodOptions();

        $configuredApis = Helpers::getConfiguredApis();
        $vendorOptions  = [];

        foreach($configuredApis as $vendor => $api)
        {
            $vendorOptions[$vendor] = Helpers::vendorToDisplay($vendor);
        }

        $this->availableValues['vendor'] = $vendorOptions;
    }


    public function create()
    {
        $addonConfig     = (new AddonModuleRepository())->getModuleConfiguration();
        $mode            = $this->formData['mode'];
        $vendor          = trim($this->formData['vendor']);
        $generateInvoice = (bool)$this->formData['generateInvoice'];
        $configuredApis  = Helpers::getConfiguredApis();

        if(!isset($configuredApis[$vendor]))
        {
            throw new \Exception('apiNotConfigured');
        }

        $configuredApi   = $configuredApis[$vendor];
        $localOrdersData = [];

        if($mode == 'single')
        {
            $remoteOrderId = $this->formData['remoteOrderId'];
            $clientId      = (int)$this->formData['clientId'];
            $payMethod     = trim($this->formData['payMethod']);

            $localOrdersData[$remoteOrderId] = [
                'remoteOrderId' => $remoteOrderId,
                'clientId'      => $clientId,
                'payMethod'     => $payMethod,
            ];
        }
        else
        {
            $file       = Request::getFacadeRoot()->files->all()['formData']['csv'];
            $csvRows    = array_map('str_getcsv', file($file));
            $header     = array_shift($csvRows);
            $dictionary = [
                'API order ID'   => 'remoteOrderId',
                'Customer ID'    => 'clientId',
                'Payment method' => 'payMethod',
            ];

            foreach($header as $index => $name)
            {
                $header[$index] = $dictionary[$name];
            }

            foreach($csvRows as $csvRow)
            {
                if(count($csvRow) != count($header))
                {
                    continue;
                }

                $localOrderData                                    = array_combine($header, $csvRow);
                $localOrdersData[$localOrderData['remoteOrderId']] = $localOrderData;
            }
        }

        $remoteOrderIds = array_keys($localOrdersData);
        $errorsCount    = 0;

        try
        {
            $remoteOrdersData = $configuredApi->exportOrder($remoteOrderIds);
        }
        catch(\Throwable $e)
        {
            throw new \Exception($e->getMessage());
        }

        foreach($localOrdersData as $remoteOrderId => $localOrderData)
        {
            $clientId  = (int)$localOrderData['clientId'];
            $payMethod = trim($localOrderData['payMethod']);

            $existingOrder = Capsule::table('tblsslorders')->where('remoteid', $remoteOrderId)->first();

            if($existingOrder)
            {
                Logger::error('orderExists', $localOrderData);
                $errorsCount++;
                continue;
            }

            $client = Client::find($clientId);

            if(!$client)
            {
                Logger::error('invalidClientId', $localOrderData);
                $errorsCount++;
                continue;
            }

            $payMethodName = PaymentGateway::where('gateway', $payMethod)->where('setting', 'name')->first()->value;

            if(!trim($payMethodName))
            {
                Logger::error('invalidPayMethod', $localOrderData);
                $errorsCount++;
                continue;
            }

            $remoteOrderData = $remoteOrdersData[$remoteOrderId];

            if(empty($remoteOrderData))
            {
                Logger::error('invalidOrderId', $localOrderData);
                $errorsCount++;
                continue;
            }

            //Helpers::debugLog('ImportProvider', '$remoteOrderData', $remoteOrderData);

            $remoteProductId = $remoteOrderData['items'][0]['product_id'];
            $domain          = trim($remoteOrderData['items'][0]['domains'][0]['name']);
            $remoteProduct   = RemoteProduct::where('remoteId', $remoteProductId)->where('vendor', $vendor)->first();

            if(empty($remoteProduct))
            {
                Logger::error('productNotSynchronized', $localOrderData);
                $errorsCount++;
                continue;
            }

            $whmcsProduct = $remoteProduct->getWhmcsProduct();

            if($whmcsProduct === false)
            {
                Logger::error('productNotImported', $localOrderData);
                $errorsCount++;
                continue;
            }

            try
            {
                $result = WhmcsApi::run('AddOrder', [
                    'clientid'       => $clientId,
                    'paymentmethod'  => $payMethod,
                    'pid'            => [$whmcsProduct->id],
                    'domain'         => [$domain],
                    'noinvoice'      => !$generateInvoice,
                    'noemail'        => true,
                    'noinvoiceemail' => true,
                ]);
            }
            catch(\Throwable $e)
            {
                Logger::error($e->getMessage(), $localOrderData);
                $errorsCount++;
                continue;
            }

            $serviceId = (int)$result['serviceids'];
            $service   = Service::find($serviceId);
            $invoiceId = (int)$result['invoiceid'];

            if(!$serviceId || !$service)
            {
                Logger::error('invalidServiceId', $localOrderData);
                $errorsCount++;
                continue;
            }

            if(isset($remoteOrderData['items'][0]['subscription']['end']))
            {
                $parts                    = explode('T', $remoteOrderData['items'][0]['subscription']['end']);
                $nextDueDate              = $parts[0];
                $service->nextduedate     = $nextDueDate;
                $service->nextinvoicedate = $nextDueDate;
                $service->save();
            }

            $_SESSION['TTSGGSModule']['importedServices'][] = $serviceId;

            try
            {
                $result = WhmcsApi::run('ModuleCreate', [
                    'serviceid' => $serviceId,
                ]);
            }
            catch(\Throwable $e)
            {
                Logger::error($e->getMessage(), $localOrderData);
                $errorsCount++;
                continue;
            }

            Capsule::table('tblsslorders')->where('serviceid', $serviceId)->update(
                [
                    'remoteid'   => $remoteOrderId,
                    'status'     => 'Configuration Submitted',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]
            );

            $orderParams = [
                'product'     => [
                    'id'   => $remoteOrderData['items'][0]['product_id'],
                    'term' => Helpers::billingPeriodToMonths($service->billingCycle),
                ],
                'csr'         => $remoteOrderData['items'][0]['csr'],
                'common_name' => [
                    'name'              => $remoteOrderData['items'][0]['domains'][0]['name'],
                    'validation_method' => ($remoteOrderData['items'][0]['domains'][0]['method'] == 'email') ? $remoteOrderData['items'][0]['domains'][0]['validation']['value'] : $remoteOrderData['items'][0]['domains'][0]['method'],
                ],
                'contacts'    => [
                    'administrator' => $remoteOrderData['items'][0]['contacts']['administrator'],
                    'technical'     => $remoteOrderData['items'][0]['contacts']['technical'],
                ]
            ];

            //Helpers::debugLog('ImportProvider', '$orderParams for addOrder', $orderParams);

            RequestModel::insert(
                [
                    'name'        => 'addOrder',
                    'serviceid'   => $serviceId,
                    'invoiceid'   => $invoiceId,
                    'api_price'   => $remoteOrderData['order']['total_amount'],
                    'whmcs_price' => 0,
                    'rate'        => $addonConfig['financeSettings']['rate'] ?: 1,
                    'diff_price'  => 0,
                    'status'      => $remoteOrderData['order']['status'],
                    'request'     => \encrypt(json_encode($orderParams)),
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s')
                ]
            );

            $orderData = $configuredApi->getOrder($remoteOrderId);

            $orderData['orderData'] = $orderParams;
            $orderFiles             = [];

            try
            {
                $orderFiles = $configuredApi->getCertificateFiles($remoteOrderId);
            }
            catch(\Exception $exception)
            {
            }

            $request = ['orderData' => $orderData, 'orderFiles' => $orderFiles];

            //Helpers::debugLog('ImportProvider', '$request for certificate', $request);

            RequestModel::insert(
                [
                    'name'       => 'certificate',
                    'serviceid'  => $serviceId,
                    'request'    => \encrypt(json_encode($request)),
                    'status'     => 'pending',
                    'created_at' => date('Y - m - d H:i:s'),
                    'updated_at' => date('Y - m - d H:i:s')
                ]
            );
        }

        if($errorsCount)
        {
            throw new \Exception('productImportErrors');
        }
    }
}