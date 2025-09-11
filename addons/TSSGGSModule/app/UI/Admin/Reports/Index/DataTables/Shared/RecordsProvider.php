<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\Shared;


use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\Core\Translation\TranslatorTrait;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Service;
use Illuminate\Database\Capsule\Manager as Capsule;

class RecordsProvider
{
    use TranslatorTrait;

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

        if($filters['sslStatus'])
        {
            $query->where('tblhosting.domainstatus', $filters['sslStatus']);
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
                                    'tblhosting.id as serviceId',
                                    'tblhosting.regdate',
                                    'tblhosting.nextduedate',
                                    'tblhosting.domain',
                                    'tblhosting.domainstatus',
                                    'tblproducts.name',
                                    'tblproducts.id as productId',
                                    'tblproducts.configoption2',
                                    'tblproducts.configoption3',
                                    'tblproducts.configoption4',
                                    'TSSGGSModule_Requests.api_price',
                                    'TSSGGSModule_Requests.rate',
                                    'TSSGGSModule_Requests.whmcs_price',
                                    'TSSGGSModule_Requests.diff_price',
                                    'TSSGGSModule_Requests.status as orderStatus',
                                    'TSSGGSModule_Requests.request as requestEncoded'
                                ])
                       ->get();

        $rows = [];

        foreach($items as $item)
        {
            $requestEncoded  = $item->requestEncoded;
            $orderRequest    = decrypt($requestEncoded);
            $serviceId       = $item->serviceId;
            $orderStatus     = $item->orderStatus ? ucfirst($item->orderStatus) : 'Awaiting Configuration';
            $certificateData = Capsule::table('TSSGGSModule_Requests')->where('serviceid', $serviceId)->where('name', 'certificate')->first();
            $storeId         = '-';
            $issueDate       = '-';
            $expirationDate  = '-';

            if($certificateData)
            {
                $certificateRequest = json_decode(decrypt($certificateData->request));
                $orderStatus        = ucfirst($certificateRequest->orderData->order->status);
                $storeId            = $certificateRequest->orderData->order->id ?: '-';
                $issueDate          = $certificateRequest->orderFiles->validity->begin ? date('Y-m-d', strtotime($certificateRequest->orderFiles->validity->begin)) : '-';
                $expirationDate     = $certificateRequest->orderFiles->validity->end ? date('Y-m-d', strtotime($certificateRequest->orderFiles->validity->end)) : '-';
            }

            if($filters['renewalPeriod'])
            {
                $explode = explode('_', $filters['renewalPeriod']);
                $days    = (int)$explode[1];

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

            $income               = Helpers::clientCurrencyToDefaultCurrency($income, $item->userid);
            $apiPrice             = floatval($item->api_price);
            $rate                 = floatval($item->rate ?: 1);
            $defaultCurrencyPrice = floatval($apiPrice * $rate);
            $cost                 = $defaultCurrencyPrice;
            $profit               = ($cost > 0) ? $income - $cost : 0;

            $rows[] = [
                'date'           => $item->regdate,
                'storeId'        => $storeId,
                'clientDetails'  => "{$clientName}<br><strong>{$this->translate('country')}</strong> {$item->country}",
                'productDetails' => "{$item->name}<br><strong>{$this->translate('domain')}</strong> {$item->domain}",
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
}