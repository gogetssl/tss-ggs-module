<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Models\CronCheck;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\CronStatus;
use ModulesGarden\TSSGGSModule\Core\ModuleConstants;
use ModulesGarden\TSSGGSModule\Packages\ModuleSettings\Models\ModuleSettings;

/**
 * Resellers without shell access (or without the module's own cron entry) still get the module's jobs: WHMCS's system
 * cron (AfterCronJob hook) starts them in the background. A job decides itself whether it is due and the commands lock
 * against overlapping runs, so starting all of them on every WHMCS cron run is safe. Nothing is started while the
 * module's own cron script has run recently.
 */
class CronLauncher
{
    /** the module's own cron entry counts as working when it ran within this many minutes */
    const OWN_CRON_MINUTES = 15;

    /** the hook starts the jobs at most this often (WHMCS runs its cron every 5 minutes) */
    const MIN_INTERVAL_SECONDS = 240;

    public static function launch()
    {
        try
        {
            if(PHP_SAPI !== 'cli' || stripos(PHP_OS, 'WIN') === 0 || !self::canExec() || self::ownCronWorks() || !self::throttle())
            {
                return;
            }

            $php    = self::phpBinary();
            $script = ModuleConstants::getFullPath('cron', 'cron.php');
            foreach(CronStatus::TASKS as $task)
            {
                exec('nohup ' . escapeshellarg($php) . ' -q ' . escapeshellarg($script) . ' ' . escapeshellarg($task) . ' > /dev/null 2>&1 &');
            }
        }
        catch(\Throwable $e)
        {
            // a failing launcher must never break WHMCS's own cron run
        }
    }

    /**
     * Whether the reseller's own cron lines started a job recently: a job ran in the last OWN_CRON_MINUTES and not
     * within a minute after this launcher's own last start (jobs started here run right after the marker is touched)
     */
    protected static function ownCronWorks()
    {
        $lastRun = 0;
        foreach(CronStatus::TASKS as $task)
        {
            $row = CronCheck::where('type', $task)->first();
            if($row && $row->last_run)
            {
                $lastRun = max($lastRun, (int)strtotime((string)$row->last_run));
            }
        }
        $marker = ModuleConstants::getFullPath('storage', 'cron_hook_last_start');
        $ours   = is_file($marker) ? filemtime($marker) : 0;

        return $lastRun > time() - self::OWN_CRON_MINUTES * 60 && $lastRun > $ours + 600;
    }

    protected static function canExec()
    {
        $disabled = array_map('trim', explode(',', (string)ini_get('disable_functions')));

        return function_exists('exec') && !in_array('exec', $disabled, true);
    }

    protected static function phpBinary()
    {
        // the cron is a CLI process: its own binary is the CLI one (PHP_BINARY can be php-fpm / cgi elsewhere)
        return PHP_BINARY !== '' && is_executable(PHP_BINARY) && stripos(basename(PHP_BINARY), 'fpm') === false ? PHP_BINARY : 'php';
    }

    /**
     * One start per interval: the time of the last start is the modification time of a marker file
     */
    protected static function throttle()
    {
        $marker = ModuleConstants::getFullPath('storage', 'cron_hook_last_start');
        if(is_file($marker) && filemtime($marker) > time() - self::MIN_INTERVAL_SECONDS)
        {
            return false;
        }

        return @touch($marker);
    }
}
