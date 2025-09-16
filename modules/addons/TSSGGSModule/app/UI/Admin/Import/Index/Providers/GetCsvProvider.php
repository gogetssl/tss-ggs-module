<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Import\Index\Providers;

use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;

class GetCsvProvider extends CrudProvider
{
    public function read()
    {
        $filename = 'template.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        fputcsv($output, ['API order ID', 'Customer ID', 'Payment method']);
        die();
    }
}