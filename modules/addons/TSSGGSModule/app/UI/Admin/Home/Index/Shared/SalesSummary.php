<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared;

use ModulesGarden\TSSGGSModule\App\Libs\Edition;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\Shared\RecordsProvider;

/**
 * Last month's sales figures; computed once per request (KPI tiles + Sales overview card)
 */
class SalesSummary
{
    protected static $data = null;

    /**
     * ['paid', 'unpaid', 'overdue' => invoiced amounts, 'profit' => profit of the paid sales,
     *  'cost', 'costedSales' => reseller cost and sales of the invoiced rows whose cost is known,
     *  'issued' => certificates issued in the period, 'expiring' => active services due within 90 days]
     */
    public static function get(): array
    {
        if(self::$data === null)
        {
            $from    = date('Y-m-d', strtotime('-1 month'));
            $data    = ['paid' => 0, 'unpaid' => 0, 'overdue' => 0, 'profit' => 0, 'cost' => 0, 'costedSales' => 0, 'issued' => 0, 'expiring' => 0];
            $records = (new RecordsProvider())->getRecords(['fromDate' => $from, 'toDate' => date('Y-m-d')]);
            $issued  = [];

            foreach($records as $record)
            {
                // a certificate is counted once, however many invoices its service has
                if($record['issueDate'] !== '-' && $record['issueDate'] >= $from)
                {
                    $issued[(int)$record['serviceId']] = true;
                }

                // cancelled, refunded and draft invoices are no sales
                $status = strtolower((string)$record['paymentStatus']);
                if(!in_array($status, ['paid', 'unpaid', 'overdue'], true))
                {
                    continue;
                }
                $data[$status] += $record['salesAmount'];
                if($status === 'paid')
                {
                    $data['profit'] += $record['grossProfit'];
                }
                // the margin is taken over the rows with a known reseller cost only
                if($record['cost'] > 0)
                {
                    $data['cost']        += $record['cost'];
                    $data['costedSales'] += $record['salesAmount'];
                }
            }
            $data['issued'] = count($issued);

            // issued certificates only; the next due date of their service follows the end of the certificate
            $requests         = Edition::table('Requests');
            $data['expiring'] = (int)\WHMCS\Database\Capsule::table('tblhosting')
                ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
                ->where('tblproducts.servertype', 'TSSGGSModule')
                ->where('tblhosting.domainstatus', 'Active')
                ->whereBetween('tblhosting.nextduedate', [date('Y-m-d'), date('Y-m-d', strtotime('+90 days'))])
                ->whereExists(function($issued) use ($requests) {
                    $issued->select(\WHMCS\Database\Capsule::raw(1))->from($requests)
                        ->whereColumn($requests . '.serviceid', 'tblhosting.id')
                        ->where($requests . '.status', 'active');
                })
                ->count();
            self::$data       = $data;
        }

        return self::$data;
    }
}
