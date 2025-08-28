<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\Shared;


use ModulesGarden\TTSGGSModule\App\Libs\Helpers;
use ModulesGarden\TTSGGSModule\Core\Translation\TranslatorTrait;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Service;
use Illuminate\Database\Capsule\Manager as Capsule;

class RecordsProvider
{
    use TranslatorTrait;

    public function getRecords($filters = [])
    {
        $query = Capsule::table('tblinvoiceitems')
                        ->join('tblhosting', 'tblinvoiceitems.relid', '=', 'tblhosting.id')
                        ->join('tblclients', 'tblinvoiceitems.userid', '=', 'tblclients.id')
                        ->join('tblinvoices', 'tblinvoiceitems.invoiceid', '=', 'tblinvoices.id')
                        ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
                        ->leftJoin('TTSGGSModule_Requests', function($join) {
                            $join->on('TTSGGSModule_Requests.serviceid', '=', 'tblhosting.id');
                            $join->on('TTSGGSModule_Requests.invoiceid', '=', 'tblinvoiceitems.invoiceid');
                        })
                        ->where('tblinvoiceitems.type', 'Hosting')
                        ->where('tblproducts.servertype', 'TTSGGSModule');


        if($filters['renewalPeriod'])
        {
            $explode = explode('_', $filters['renewalPeriod']);
            $days    = (int)$explode[1];

            if($explode[0] == 'last')
            {
                $query->where('tblhosting.nextduedate', '<', date('Y-m-d'));
                if($days)
                {
                    $query->where('tblhosting.nextduedate', '>', date('Y-m-d', strtotime('-' . $days . ' day')));
                }
            }
            elseif($explode[0] == 'next')
            {
                $query->where('tblhosting.nextduedate', '>', date('Y-m-d'));
                if($days)
                {
                    $query->where('tblhosting.nextduedate', '<', date('Y-m-d', strtotime('+' . $days . ' day')));
                }
            }
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
                                    'tblhosting.regdate',
                                    'tblhosting.nextduedate',
                                    'tblhosting.domain',
                                    'tblhosting.domainstatus',
                                    'tblproducts.name',
                                    'tblproducts.id as productId',
                                    'tblproducts.configoption2',
                                    'tblproducts.configoption3',
                                    'tblproducts.configoption4',
                                    'TTSGGSModule_Requests.api_price',
                                    'TTSGGSModule_Requests.rate',
                                    'TTSGGSModule_Requests.whmcs_price',
                                    'TTSGGSModule_Requests.diff_price',
                                    'TTSGGSModule_Requests.status as orderStatus'
                                ])
                       ->get();

        $rows = [];

        foreach($items as $item)
        {
            $clientName = $item->companyname ?: $item->firstname . ' ' . $item->lastname;
            $income     = $item->amount;

            if($item->taxed)
            {
                $income = Helpers::getTaxedValue($income, $item->taxrate, $item->taxrate2);
            }

            $income = Helpers::clientCurrencyToSelectedCurrency($income, $item->userid);
            //$cost   = Helpers::apiCurrencyToSelectedCurrency($item->api_price);
            $apiPrice = floatval($item->api_price);
            $rate     = floatval($item->rate);
            $cost     = floatval($apiPrice * $rate);
            $profit   = ($cost > 0) ? $income - $cost : 0;

            $rows[] = [
                'date'           => $item->regdate,
                'storeId'        => $item->configoption2,
                'clientDetails'  => "{$clientName}<br><strong>{$this->translate('country')}</strong> {$item->country}",
                'productDetails' => "{$item->name}<br><strong>{$this->translate('domain')}</strong> {$item->domain}",
                'productId'      => $item->productId,
                'productName'    => $item->name,
                'type'           => $item->configoption4,
                'brand'          => $item->configoption3,
                'status'         => $item->orderStatus ? ucfirst($item->orderStatus) : 'Awaiting Configuration',
                'issueDate'      => $item->regdate,
                'expirationDate' => $item->nextduedate,
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