<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\Shared\RecordsProvider;

/**
 * Last month's sales figures; computed once per request (KPI tiles + Sales overview card)
 */
class SalesSummary
{
    protected static $data = null;

    /** ['paid', 'unpaid', 'overdue', 'profit' => float, 'issued', 'expiring' => int] */
    public static function get(): array
    {
        if(self::$data === null)
        {
            $data    = ['paid' => 0, 'unpaid' => 0, 'overdue' => 0, 'profit' => 0, 'issued' => 0, 'expiring' => 0];
            $records = (new RecordsProvider())->getRecords(['fromDate' => date('Y-m-d', strtotime('-1 month')), 'toDate' => date('Y-m-d')]);

            foreach($records as $record)
            {
                $status = strtolower((string)$record['paymentStatus']);
                if(isset($data[$status]) && in_array($status, ['paid', 'unpaid', 'overdue'], true))
                {
                    $data[$status] += $record['salesAmount'];
                }
                $data['profit'] += $record['grossProfit'];
                $data['issued']++;
            }

            $data['expiring'] = count((new RecordsProvider())->getRecords(['renewalPeriod' => 'next_90']));
            self::$data       = $data;
        }

        return self::$data;
    }
}
