<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\Renewal\Providers;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\Renewal\ArrayDataProvider\DataProvider;
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
        $records         = $recordsProvider->getRecords($filters);
        $fields          = [
            'date',
            'storeId',
            'clientDetails',
            'productDetails',
            'type',
            'brand',
            'status',
            'issueDate',
            'expirationDate',
        ];

        $output = fopen('php://output', 'w');

        foreach($records as $record)
        {
            $csvRow = [];
            foreach($fields as $field)
            {
                $csvRow[] = strip_tags(str_replace('<br>',' ',$record[$field]));
            }

            fputcsv($output, $csvRow);
        }
        die();
    }
}