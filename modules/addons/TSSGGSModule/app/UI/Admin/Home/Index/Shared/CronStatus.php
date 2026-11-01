<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared;

use ModulesGarden\TSSGGSModule\App\Models\CronCheck;
use ModulesGarden\TSSGGSModule\Packages\ModuleSettings\Models\ModuleSettings;

/**
 * Last run of the module cron and its scheduled tasks; read once per request (attention banner + System health card)
 */
class CronStatus
{
    /** the jobs, in the order RunAll starts them (ReSyncProducts is part of ProductPricingUpdate now) */
    const TASKS = [
        'ProductPricingUpdate',
        'SSLCertificatesProcessing',
        'SSLCertificates',
        'RenewalNotifyCertificate',
        'SSLAutoInstall',
    ];

    protected static $rows = null;

    /** name => ['last' => 'Y-m-d H:i:s' | null (never), 'error' => string] */
    public static function rows(): array
    {
        if(self::$rows === null)
        {
            // (the framework's task runner cronScript.php is not listed: nothing is queued for it in this module, so its
            // "never" said nothing about the jobs)
            self::$rows = [];

            foreach(self::TASKS as $task)
            {
                $check              = CronCheck::where('type', $task)->first();
                self::$rows[$task] = [
                    'last'  => $check ? (string)$check->last_run : null,
                    'error' => $check ? trim((string)$check->last_error) : '',
                ];
            }
        }

        return self::$rows;
    }

    /** All tasks have run at least once */
    public static function ok(): bool
    {
        foreach(self::rows() as $row)
        {
            if($row['last'] === null)
            {
                return false;
            }
        }

        return true;
    }

    /** Every task has run and none ended with an error */
    public static function healthy(): bool
    {
        foreach(self::rows() as $row)
        {
            if($row['last'] === null || $row['error'] !== '')
            {
                return false;
            }
        }

        return true;
    }

    /** Time of the most recent run of any task ('Y-m-d H:i:s'), null when nothing has run */
    public static function latest()
    {
        $latest = null;
        foreach(self::rows() as $row)
        {
            if($row['last'] !== null && ($latest === null || strcmp($row['last'], $latest) > 0))
            {
                $latest = $row['last'];
            }
        }

        return $latest;
    }
}
