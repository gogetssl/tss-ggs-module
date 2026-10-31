<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared;

use ModulesGarden\TSSGGSModule\App\Models\CronCheck;
use ModulesGarden\TSSGGSModule\Packages\ModuleSettings\Models\ModuleSettings;

/**
 * Last run of the module cron and its scheduled tasks; read once per request (attention banner + System health card)
 */
class CronStatus
{
    const TASKS = [
        'ProductPricingUpdate',
        'SSLCertificates',
        'RenewalNotifyCertificate',
        'ReSyncProducts',
        'SSLCertificatesProcessing',
        'SSLAutoInstall',
    ];

    protected static $rows = null;

    /** name => ['last' => 'Y-m-d H:i:s' | null (never), 'error' => string] */
    public static function rows(): array
    {
        if(self::$rows === null)
        {
            $timestamp  = ModuleSettings::where('setting', 'cronLastUpdate')->value('value');
            self::$rows = ['cronScript' => ['last' => $timestamp ? date('Y-m-d H:i:s', (int)$timestamp) : null, 'error' => '']];

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
}
