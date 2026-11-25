<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Certificates;

use ModulesGarden\TSSGGSModule\App\Libs\Gateway\SSLTrustCenterApi;
use ModulesGarden\TSSGGSModule\App\Libs\Support\Helpers;
use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TSSGGSModule\App\Models\Request as RequestModel;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\App\Libs\Support\WhmcsApi;
use ModulesGarden\TSSGGSModule\App\Models\Whmcs\Client;
use ModulesGarden\TSSGGSModule\App\Models\Whmcs\PaymentGateway;
use ModulesGarden\TSSGGSModule\App\Models\Whmcs\Service;
use ModulesGarden\TSSGGSModule\App\Libs\Logs\Logger;
use WHMCS\Database\Capsule;

/**
 * Import of certificates that were ordered outside WHMCS (admin page Import): each vendor order becomes a service
 * of the chosen client - a new WHMCS order, or an existing service of the client - linked to its certificate.
 * What could not be imported is written to the module log; run() returns how many orders that were.
 */
class OrderImport
{
    /** columns of the CSV file => keys of an order to import */
    const CSV_COLUMNS = ['API order ID' => 'remoteOrderId', 'Customer ID' => 'clientId', 'Payment method' => 'payMethod', 'Service ID' => 'serviceId'];

    /** @return array[] remote order ID => [remoteOrderId, clientId, payMethod, serviceId], from an uploaded CSV file */
    public static function fromCsv(string $file): array
    {
        $rows   = array_map('str_getcsv', file($file) ?: []);
        $header = [];
        foreach((array)array_shift($rows) as $name)
        {
            $header[] = self::CSV_COLUMNS[trim((string)$name)] ?? trim((string)$name);
        }
        $orders = [];
        foreach($rows as $row)
        {
            if(count($row) != count($header))
            {
                continue;
            }
            $order = array_combine($header, $row);
            if(isset($order['remoteOrderId']))
            {
                $orders[$order['remoteOrderId']] = $order;
            }
        }

        return $orders;
    }

    /** The CSV file to fill in: its header line */
    public static function csvTemplate(): string
    {
        $out = fopen('php://temp', 'w+');
        fputcsv($out, array_keys(self::CSV_COLUMNS));
        rewind($out);

        return (string)stream_get_contents($out);
    }

    /**
     * @param array[] $localOrdersData remote order ID => [remoteOrderId, clientId, payMethod, serviceId]
     * @return int orders that could not be imported (the reasons are in the module log)
     * @throws \Exception when the vendor API is not configured or does not answer
     */
    public static function run(array $localOrdersData, bool $generateInvoice, string $vendor = ''): int
    {
        $addonConfig    = (new AddonModuleRepository())->getModuleConfiguration();
        $configuredApis = Helpers::getConfiguredApis();
        // the provider is not asked for: the order is looked up at the configured gateway
        $vendor         = trim($vendor) ?: (string)key($configuredApis);
        if(!isset($configuredApis[$vendor]))
        {
            throw new \Exception('apiNotConfigured');
        }
        $configuredApi = $configuredApis[$vendor];

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

            if(empty($remoteOrderData) || empty($remoteOrderData['items'][0]['id']))
            {
                Logger::error('invalidOrderId', $localOrderData);
                $errorsCount++;
                continue;
            }

            // tblsslorders.remoteid holds the certificate (item) ID, older services may still have the order ID
            $remoteItemId  = SSLTrustCenterApi::getItemId($remoteOrderData);
            $existingOrder = Capsule::table('tblsslorders')->whereIn('remoteid', [$remoteItemId, $remoteOrderId])->first();

            if($existingOrder)
            {
                Logger::error('orderExists', $localOrderData);
                $errorsCount++;
                continue;
            }


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

            $mapToService = (int)($localOrderData['serviceId'] ?? 0);
            if($mapToService)
            {
                // Attach the order to an existing service of the client (e.g. created by hand or by another module)
                $service = Service::find($mapToService);
                if(!$service || (int)$service->userid !== $clientId || $service->product->servertype !== 'TSSGGSModule')
                {
                    Logger::error('invalidServiceToMap', $localOrderData);
                    $errorsCount++;
                    continue;
                }
                $sslOrder = Capsule::table('tblsslorders')->where('serviceid', $mapToService)->first();
                if($sslOrder && !empty($sslOrder->remoteid) && $sslOrder->status !== 'Awaiting Configuration')
                {
                    Logger::error('serviceHasOrder', $localOrderData);
                    $errorsCount++;
                    continue;
                }

                $serviceId       = $mapToService;
                $invoiceId       = 0;
                $service->domain = $domain;
                $service->save();
            }
            else
            {
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
                    Logger::error($e->getMessage(), \ModulesGarden\TSSGGSModule\App\Libs\Support\LogContext::exception($e, $localOrderData));
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
            }

            if(isset($remoteOrderData['items'][0]['subscription']['end']))
            {
                $parts                    = explode('T', $remoteOrderData['items'][0]['subscription']['end']);
                $nextDueDate              = $parts[0];
                $service->nextduedate     = $nextDueDate;
                $service->nextinvoicedate = $nextDueDate;
                $service->save();
            }

            $_SESSION['TSSGGSModule']['importedServices'][] = $serviceId;

            // A mapped service usually has its SSL order row already - ModuleCreate refuses to run twice
            if(!Capsule::table('tblsslorders')->where('serviceid', $serviceId)->exists())
            {
                try
                {
                    $result = WhmcsApi::run('ModuleCreate', [
                        'serviceid' => $serviceId,
                    ]);
                }
                catch(\Throwable $e)
                {
                    Logger::error($e->getMessage(), \ModulesGarden\TSSGGSModule\App\Libs\Support\LogContext::exception($e, $localOrderData));
                    $errorsCount++;
                    continue;
                }
            }

            Capsule::table('tblsslorders')->where('serviceid', $serviceId)->update(
                [
                    'remoteid'   => $remoteItemId,
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

            // A mapped service may carry the data of an earlier order
            RequestModel::where('serviceid', $serviceId)->whereIn('name', ['addOrder', 'certificate'])->delete();

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

            $orderData = $configuredApi->getOrder($remoteItemId);

            $orderData['orderData'] = $orderParams;
            $orderFiles             = [];

            try
            {
                $orderFiles = $configuredApi->getCertificateFiles($remoteItemId);
            }
            catch(\Exception $exception)
            {
            }

            $request = ['orderData' => $orderData, 'orderFiles' => $orderFiles];


            RequestModel::insert(
                [
                    'name'       => 'certificate',
                    'serviceid'  => $serviceId,
                    'request'    => \encrypt(json_encode($request)),
                    'status'     => 'pending',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            );
        }

        return $errorsCount;
    }
}
