<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index\Shared;


use ModulesGarden\TTSGGSModule\App\Libs\Helpers;
use ModulesGarden\TTSGGSModule\App\Models\CronCheck;
use ModulesGarden\TTSGGSModule\Core\Translation\TranslatorTrait;

use Illuminate\Database\Capsule\Manager as Capsule;
use ModulesGarden\TTSGGSModule\Packages\Logs\Models\Logs;

class LogProvider
{
    use TranslatorTrait;

    public function getLog($data = [])
    {
        //Helpers::debugLog('LogProvider - getLog', '$data', $data);

        $expectedFields = [
            'whmcsUrl'            => 'WHMCS URL:',
            'moduleVersion'       => 'Module Version:',
            'phpVersion'          => 'PHP Version:',
            'tssPartnerId'        => 'TSS Partner ID:',
            'ggsPartnerId'        => 'GGS Partner ID:',
            //'cronStatus'          => 'CRON Status:',
            'fromDate'            => 'From Date:',
            'toDate'              => 'To Date:',
            //'modifiedModuleFiles' => 'Modified Files:',
            'issueDetails'        => 'Issue Details:',

        ];


        $log = [];

        foreach($expectedFields as $key => $display)
        {
            if(empty($data[$key]))
            {
                continue;
            }

            $log[] = $display;
            $log[] = $data[$key];
            $log[] = '';
        }

        $expectedCrons = [
            'ProductPricingUpdate',
            'SSLCertificates'
        ];

        $log[] = 'Crons:';

        foreach($expectedCrons as $expectedCron)
        {
            $cronCheck = CronCheck::where('type', $expectedCron)->first();

            if($cronCheck)
            {
                $date  = $cronCheck->last_run;
                $error = trim($cronCheck->last_error);
                $item  = $expectedCron . ' - Last Run: ' . $date;

                if($error)
                {
                    $item .= ' - Error: ' . $error;
                }

                $log[] = $item;
            }
            else
            {
                $log[] = $expectedCron . ' - Last Run: Never';
            }
        }

        $log[] = '';
        $log[] = 'Logs:';

        $rows = Logs::where('date', '>=', $data['fromDate'] . ' 00:00:00')
                    ->where('date', '<=', $data['toDate'] . ' 23:59:59')
                    ->select('id', 'type', 'date', 'data', 'message')
                    ->get();

        //Helpers::debugLog('LogProvider - getLog', '$rows', $rows);

        foreach($rows as $row)
        {
            $log[] = $row->date . ' - ' . $row->type . ' - ' . $row->message;
        }

        $checksumCompare = Helpers::compareChecksumData();

        if($checksumCompare['new'] || $checksumCompare['modified'] || $checksumCompare['deleted'])
        {
            $log[] = '';
            $log[] = 'Modified Files:';

            unset($checksumCompare['unchanged']);

            foreach($checksumCompare as $key => $files)
            {
                if(!empty($files))
                {
                    $log[] = ucfirst($key).':';
                    foreach($files as $file)
                    {
                        $log[] = $file;
                    }
                }
            }
        }

        return implode("\n", $log);
    }
}