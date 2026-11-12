<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\Shared;


use ModulesGarden\TSSGGSModule\App\Libs\AcmeHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\AisHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\Models\AcmeSubscription;
use ModulesGarden\TSSGGSModule\App\Models\AisSubscription;
use ModulesGarden\TSSGGSModule\Core\Translation\TranslatorTrait;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Service;
use Illuminate\Database\Capsule\Manager as Capsule;

class RecordsProvider
{
    use TranslatorTrait;

    protected static function e($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * Sends the report as a CSV download: the given columns of the rows the filters select
     */
    public function outputCsv($filters, array $fields, $onlyPaid = false): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=report.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, array_map(function($field) {
            return ucwords(preg_replace('/(?<!^)([A-Z])/', ' $1', $field));
        }, $fields));

        foreach($this->getRecords($filters, $onlyPaid) as $record)
        {
            $row = [];
            foreach($fields as $field)
            {
                // the table cells are HTML: a file gets the plain text
                $row[] = html_entity_decode(strip_tags(str_replace('<br>', ' ', (string)$record[$field])), ENT_QUOTES, 'UTF-8');
            }
            fputcsv($output, $row);
        }
    }

    public function getRecords($filters = [], $onlyPaid = false)
    {
        $query = Capsule::table('tblinvoiceitems')
                        ->join('tblhosting', 'tblinvoiceitems.relid', '=', 'tblhosting.id')
                        ->join('tblclients', 'tblinvoiceitems.userid', '=', 'tblclients.id')
                        ->join('tblinvoices', 'tblinvoiceitems.invoiceid', '=', 'tblinvoices.id')
                        ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
                        ->leftJoin('TSSGGSModule_Requests', function($join) {
                            $join->on('TSSGGSModule_Requests.serviceid', '=', 'tblhosting.id');
                            $join->on('TSSGGSModule_Requests.invoiceid', '=', 'tblinvoiceitems.invoiceid');
                        })
                        ->where('tblinvoiceitems.type', 'Hosting')
                        ->where('tblproducts.servertype', 'TSSGGSModule');

        if($onlyPaid)
        {
            $query->where('tblinvoices.status', 'Paid');
        }

        // the CSV forms pass their data as a container object
        if($filters instanceof \ModulesGarden\TSSGGSModule\Core\Data\Container)
        {
            $filters = $filters->toArray();
        }
        $filters = (array)$filters + ['fromDate' => '', 'toDate' => '', 'productId' => '', 'brand' => '', 'sslStatus' => '', 'vendorOrderId' => '', 'renewalPeriod' => ''];

        if($filters['fromDate'])
        {
            $query->where('tblinvoices.date', '>=', $filters['fromDate']);
        }

        if($filters['toDate'])
        {
            $query->where('tblinvoices.date', '<=', $filters['toDate']);
        }

        if($filters['productId'])
        {
            $query->where('tblhosting.packageid', (int)$filters['productId']);
        }

        if($filters['brand'])
        {
            $query->where('tblproducts.configoption3', $filters['brand']);
        }

        $items = $query->select([
                                    'tblinvoiceitems.amount',
                                    'tblinvoiceitems.taxed',
                                    'tblinvoiceitems.userid',
                                    'tblinvoices.date',
                                    'tblinvoices.paymentmethod',
                                    'tblinvoices.status',
                                    'tblinvoices.taxrate',
                                    'tblinvoices.taxrate2',
                                    'tblclients.firstname',
                                    'tblclients.lastname',
                                    'tblclients.companyname',
                                    'tblclients.country',
                                    'tblclients.currency as clientCurrency',
                                    'tblhosting.id as serviceId',
                                    'tblhosting.regdate',
                                    'tblhosting.nextduedate',
                                    'tblhosting.domain',
                                    'tblhosting.domainstatus',
                                    'tblproducts.name',
                                    'tblproducts.id as productId',
                                    'tblproducts.configoption1',
                                    'tblproducts.configoption2',
                                    'tblproducts.configoption3',
                                    'tblproducts.configoption4',
                                    'TSSGGSModule_Requests.api_price',
                                    'TSSGGSModule_Requests.rate',
                                    'TSSGGSModule_Requests.whmcs_price',
                                    'TSSGGSModule_Requests.diff_price',
                                    'TSSGGSModule_Requests.status as orderStatus',
                                ])
                       ->get();

        // the stored certificates of all listed services in one query (a service is listed once per invoice)
        $serviceIds   = array_values(array_unique(array_map(function($item) { return (int)$item->serviceId; }, $items->all())));
        $certificates = [];
        foreach(array_chunk($serviceIds, 1000) as $chunk)
        {
            foreach(Capsule::table('TSSGGSModule_Requests')->whereIn('serviceid', $chunk)->where('name', 'certificate')->get(['serviceid', 'request']) as $row)
            {
                $certificates[(int)$row->serviceid] = $row->request;
            }
        }
        $defaultCurrencyId = (int)Helpers::getDefaultCurrency()->id;

        $rows         = [];
        $productTypes = [];
        $states       = [];

        foreach($items as $item)
        {
            $serviceId = (int)$item->serviceId;
            if(!isset($states[$serviceId]))
            {
                $states[$serviceId] = $this->certificateState($item, $certificates[$serviceId] ?? null, $productTypes);
            }
            [$orderStatus, $storeId, $issueDate, $expirationDate] = $states[$serviceId];

            // the certificate status and the vendor order ID are known only now
            if($filters['sslStatus'] && strtolower((string)$orderStatus) !== strtolower((string)$filters['sslStatus']))
            {
                continue;
            }
            if(trim((string)$filters['vendorOrderId']) !== '' && stripos((string)$storeId, trim((string)$filters['vendorOrderId'])) === false)
            {
                continue;
            }

            if($filters['renewalPeriod'])
            {
                $explode = explode('_', $filters['renewalPeriod']);
                $days    = (int)($explode[1] ?? 0);

                if($explode[0] == 'last')
                {
                    if($expirationDate == '-' || $expirationDate >= date('Y-m-d'))
                    {
                        continue;
                    }

                    if($days &&  $expirationDate <= date('Y-m-d', strtotime('-' . $days . ' day')))
                    {
                        continue;
                    }
                }
                elseif($explode[0] == 'next')
                {
                    if($expirationDate == '-' || $expirationDate <= date('Y-m-d'))
                    {
                        continue;
                    }

                    if($days &&  $expirationDate >= date('Y-m-d', strtotime('+' . $days . ' day')))
                    {
                        continue;
                    }
                }
            }

            $clientName = $item->companyname ?: $item->firstname . ' ' . $item->lastname;
            $income     = $item->amount;

            if($item->taxed)
            {
                $income = Helpers::getTaxedValue($income, $item->taxrate, $item->taxrate2);
            }

            $income               = (int)$item->clientCurrency === $defaultCurrencyId ? floatval($income) : \convertCurrency(floatval($income), (int)$item->clientCurrency, $defaultCurrencyId);
            $apiPrice             = floatval($item->api_price);
            $rate                 = floatval($item->rate ?: 1);
            $defaultCurrencyPrice = floatval($apiPrice * $rate);
            $cost                 = $defaultCurrencyPrice;
            $profit               = ($cost > 0) ? $income - $cost : 0;

            $rows[] = [
                'serviceId'      => $item->serviceId,
                'serviceStatus'  => $item->domainstatus,
                'vendor'         => $item->configoption1,
                'date'           => $item->regdate,
                'storeId'        => $storeId,
                // shown as HTML: the values are escaped (WHMCS stores some of them encoded already - not encoded twice)
                'clientDetails'  => self::e($clientName) . "<br><strong>{$this->translate('country')}</strong> " . self::e($item->country),
                'productDetails' => self::e($item->name) . "<br><strong>{$this->translate('domain')}</strong> " . self::e($item->domain),
                'productId'      => $item->productId,
                'productName'    => $item->name,
                'type'           => $item->configoption4,
                'brand'          => $item->configoption3,
                'status'         => $orderStatus,
                'issueDate'      => $issueDate,
                'expirationDate' => $expirationDate,
                'salesAmount'    => $income,
                'cost'           => $cost,
                'grossProfit'    => $profit,
                'payment'        => $item->paymentmethod,
                'paymentStatus'  => $item->status
            ];
        }

        return $rows;
    }

    /**
     * [status, vendor order ID, issue date, expiration date] of a service's certificate. ACME / AIS keep their state in
     * the subscription tables, S/MIME and code signing in their own request row, SSL in the stored certificate.
     */
    protected function certificateState($item, $certificateRequest, array &$productTypes): array
    {
        $serviceId      = (int)$item->serviceId;
        $orderStatus    = $item->orderStatus ? ucfirst($item->orderStatus) : 'Awaiting Configuration';
        $storeId        = '-';
        $issueDate      = '-';
        $expirationDate = '-';

        if(!isset($productTypes[$item->productId]))
        {
            $productTypes[$item->productId] = AcmeHelpers::isAcmeWhmcsProduct($item->productId) ? 'acme' : (AisHelpers::isAisWhmcsProduct($item->productId) ? 'ais'
                : (\ModulesGarden\TSSGGSModule\App\Libs\GatewayOrderService::typeOfProduct($item->productId) ?: 'ssl'));
        }
        $type = $productTypes[$item->productId];

        $subscription = null;
        if($type === 'acme')
        {
            $subscription = AcmeSubscription::getByServiceId($serviceId);
        }
        elseif($type === 'ais')
        {
            $subscription = AisSubscription::getByServiceId($serviceId);
        }

        $date = function($value) {
            return $value ? date('Y-m-d', strtotime((string)$value)) : '-';
        };

        if($type !== 'ssl' && is_subclass_of($type, \ModulesGarden\TSSGGSModule\App\Libs\GatewayOrderService::class))
        {
            $stored         = $type::stored($serviceId);
            $orderStatus    = !empty($stored['status']) ? ucfirst($stored['status']) : $orderStatus;
            $storeId        = !empty($stored['itemId']) ? $stored['itemId'] : '-';
            $issueDate      = $date($stored['validFrom'] ?? '');
            $expirationDate = $date($stored['validTill'] ?? '');
        }
        elseif($subscription)
        {
            $orderStatus    = $subscription->status ? ucfirst($subscription->status) : $orderStatus;
            $storeId        = $subscription->api_order_id ?: '-';
            $issueDate      = $subscription->period_start ? $subscription->period_start->format('Y-m-d') : '-';
            $expirationDate = $subscription->period_end ? $subscription->period_end->format('Y-m-d') : '-';
        }
        elseif($certificateRequest)
        {
            $certificate    = json_decode((string)decrypt($certificateRequest), true) ?: [];
            $orderStatus    = !empty($certificate['orderData']['order']['status']) ? ucfirst($certificate['orderData']['order']['status']) : $orderStatus;
            $storeId        = !empty($certificate['orderData']['order']['id']) ? $certificate['orderData']['order']['id'] : '-';
            $issueDate      = $date($certificate['orderFiles']['validity']['begin'] ?? '');
            $expirationDate = $date($certificate['orderFiles']['validity']['end'] ?? '');
        }

        return [$orderStatus, $storeId, $issueDate, $expirationDate];
    }

}