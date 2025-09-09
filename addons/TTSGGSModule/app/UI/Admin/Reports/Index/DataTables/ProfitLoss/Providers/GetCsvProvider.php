<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\ProfitLoss\Providers;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\Shared\RecordsProvider;
use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;

class GetCsvProvider extends CrudProvider
{
    public function read()
    {
        $filters  = $this->formData;
        $filename = 'report.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $recordsProvider = new RecordsProvider();
        $records         = $recordsProvider->getRecords($filters, true);
        $fields          = [
            'date',
            'storeId',
            'clientDetails',
            'productDetails',
            'type',
            'brand',
            'salesAmount',
            'cost',
            'grossProfit',
            'payment',
            'paymentStatus',
        ];

        $output    = fopen('php://output', 'w');
        $csvHeader = [];

        foreach($fields as $field)
        {
            $title       = preg_replace('/(?<!^)([A-Z])/', ' $1', $field);
            $csvHeader[] = ucwords($title);
        }

        fputcsv($output, $csvHeader);

        foreach($records as $record)
        {
            $csvRow = [];
            foreach($fields as $field)
            {
                $csvRow[] = strip_tags(str_replace('<br>', ' ', $record[$field]));
            }

            fputcsv($output, $csvRow);
        }
        die();
    }
}