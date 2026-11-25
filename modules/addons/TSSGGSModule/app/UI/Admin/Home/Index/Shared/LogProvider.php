<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared;


use ModulesGarden\TSSGGSModule\App\Libs\Support\Helpers;
use ModulesGarden\TSSGGSModule\App\Models\CronCheck;
use ModulesGarden\TSSGGSModule\Core\Translation\TranslatorTrait;

use ModulesGarden\TSSGGSModule\Packages\Logs\Models\Logs;

class LogProvider
{
    use TranslatorTrait;

    /** the cron jobs the report checks (each should have run within the last day) */
    const CRONS = ['ProductPricingUpdate', 'SSLCertificates', 'RenewalNotifyCertificate', 'ReSyncProducts', 'SSLCertificatesProcessing', 'SSLAutoInstall'];

    /** "No issues" or "Issues: N" (a cron that did not run in the last day or ended with an error) */
    public static function cronStatus(): string
    {
        $issues = 0;
        foreach(self::CRONS as $cron)
        {
            $check = CronCheck::where('type', $cron)->where('last_run', '>', date('Y-m-d H:i:s', strtotime('-1 day')))->first();
            if(!$check || trim((string)$check->last_error) !== '')
            {
                $issues++;
            }
        }

        return $issues ? 'Issues: ' . $issues : 'No issues';
    }

    public function getLog($data = [])
    {

        $expectedFields = [
            'whmcsUrl'            => 'WHMCS URL:',
            'moduleVersion'       => 'Module Version:',
            'phpVersion'          => 'PHP Version:',
            'tssPartnerId'        => 'TSS Partner ID:',
            'ggsPartnerId'        => 'GGS Partner ID:',
            'cronStatus'          => 'CRON Status:',
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

        $expectedCrons = self::CRONS;

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
