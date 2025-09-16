<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Providers;

use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\LogProvider;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;


class DownloadLogProvider extends CrudProvider
{
    public function read()
    {
        $data        = $this->formData->toArray();

        //Helpers::debugLog('DownloadLogProvider', '$data', $data);

        $logProvider = new LogProvider();
        $log         = $logProvider->getLog($data);
        $filename    = 'log.txt';

        header('Content-Type: text/txt; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');

        fputs($output, $log);
        die();
    }
}