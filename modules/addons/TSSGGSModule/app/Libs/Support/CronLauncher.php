<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Support;

use ModulesGarden\TSSGGSModule\App\Models\CronCheck;
use ModulesGarden\TSSGGSModule\App\Libs\Dashboard\CronStatus;
use ModulesGarden\TSSGGSModule\App\Kernel\Kernel;

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

    /** what the last WHMCS cron run did here, shown on Settings > CRON (see state()) */
    const STATE_FILE = 'cron_hook_state.json';

    public static function launch()
    {
        try
        {
            if(PHP_SAPI !== 'cli')
            {
                return;
            }
            if(stripos(PHP_OS, 'WIN') === 0)
            {
                self::record('windows');
                return;
            }
            if(!self::canExec())
            {
                self::record('no_exec');
                return;
            }
            if(self::ownCronWorks())
            {
                self::record('own_cron');
                return;
            }
            if(!self::throttle())
            {
                self::record('throttled');
                return;
            }

            $php    = self::phpBinary();
            $script = Kernel::path('cron', 'cron.php');
            // the same single command a reseller schedules by hand: every job runs when it is due
            exec('nohup ' . escapeshellarg($php) . ' -q ' . escapeshellarg($script) . ' RunAll > /dev/null 2>&1 &');
            self::record('started');
        }
        catch(\Throwable $e)
        {
            // a failing launcher must never break WHMCS's own cron run
        }
    }

    /**
     * How the module's jobs get started, for the Settings > CRON page:
     * ['mode' => own | whmcs | whmcs_rare | blocked | none, 'reason' => windows | no_exec | '', 'hook' => ts|0 (last WHMCS cron run),
     *  'started' => ts|0 (last start by the hook), 'job' => ts|0 (last job run)]
     */
    public static function status(): array
    {
        $state  = self::state();
        // the start marker is older than the state file: right after an upgrade only the marker knows the last start
        $marker = Kernel::path('storage', 'cron_hook_last_start');
        $start  = max((int)($state['started'] ?? 0), is_file($marker) ? (int)filemtime($marker) : 0);
        $out    = ['mode' => 'none', 'reason' => '', 'hook' => (int)($state['fired'] ?? 0), 'started' => $start, 'job' => self::lastJobRun()];

        if(self::ownCronWorks())
        {
            $out['mode'] = 'own';
        }
        elseif(in_array($state['result'] ?? '', ['windows', 'no_exec'], true) && $out['hook'] > time() - 86400)
        {
            $out['mode']   = 'blocked';
            $out['reason'] = $state['result'];
        }
        elseif($out['started'] > time() - self::OWN_CRON_MINUTES * 60)
        {
            $out['mode'] = 'whmcs';
        }
        elseif($out['started'] > 0)
        {
            // the WHMCS cron starts the jobs, but not every 5 minutes (hourly / daily system cron, or it stopped)
            $out['mode'] = 'whmcs_rare';
        }

        return $out;
    }

    protected static function state(): array
    {
        $file = Kernel::path('storage', self::STATE_FILE);
        $data = is_file($file) ? json_decode((string)@file_get_contents($file), true) : null;

        return is_array($data) ? $data : [];
    }

    /** remembers what this WHMCS cron run did; "started" keeps its own time, the throttled runs in between do not reset it */
    protected static function record(string $result)
    {
        $state           = self::state();
        $state['fired']  = time();
        $state['result'] = $result;
        if($result === 'started')
        {
            $state['started'] = time();
        }
        @file_put_contents(Kernel::path('storage', self::STATE_FILE), json_encode($state), LOCK_EX);
    }

    protected static function lastJobRun(): int
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

        return $lastRun;
    }

    /**
     * Whether the reseller's own cron lines started a job recently: a job ran in the last OWN_CRON_MINUTES and not
     * within a minute after this launcher's own last start (jobs started here run right after the marker is touched)
     */
    protected static function ownCronWorks()
    {
        $lastRun = self::lastJobRun();
        $marker = Kernel::path('storage', 'cron_hook_last_start');
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
        $marker = Kernel::path('storage', 'cron_hook_last_start');
        if(is_file($marker) && filemtime($marker) > time() - self::MIN_INTERVAL_SECONDS)
        {
            return false;
        }

        return @touch($marker);
    }
}
