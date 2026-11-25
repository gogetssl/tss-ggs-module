<?php

// cron jobs are started from the command line only
if (PHP_SAPI !== 'cli')
{
    http_response_code(403);
    exit;
}

$modulePath = dirname(__DIR__);
$whmcsPath  = dirname(dirname(dirname($modulePath)));

require_once $whmcsPath . DIRECTORY_SEPARATOR . 'init.php';
require_once $modulePath . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Kernel' . DIRECTORY_SEPARATOR . 'Boot.php';

//cause WHMCS
ini_set('max_execution_time', 0);

// a module update swaps the files: no job starts meanwhile, and the update waits for running ones (they hold
// cron.lock shared until they end; App\Libs\Update\Swap::pauseCrons)
$moduleUpdateCommand = (string)(($_SERVER['argv'] ?? [])[1] ?? '') === 'ModuleUpdate'; // the update itself
if (!$moduleUpdateCommand && \TSSGGSModule\App\Libs\Update\State::locked())
{
    echo "Module update in progress - the job runs next time.\n";
    exit;
}
$moduleUpdateCronLock = null;
if (!$moduleUpdateCommand && is_dir(\TSSGGSModule\App\Libs\Update\Workdir::path()))
{
    $moduleUpdateCronLock = \TSSGGSModule\App\Libs\Update\Swap::openLock('cron.lock');
    // an update holds it exclusively while it swaps: this job then does not start at all (it would load old and new
    // files mixed)
    if ($moduleUpdateCronLock && !@flock($moduleUpdateCronLock, LOCK_SH | LOCK_NB))
    {
        echo "Module update in progress - the job runs next time.\n";
        exit;
    }
}

// an install upgraded by copying files, where WHMCS never ran the addon upgrade, is still moved over
foreach (['\\TSSGGSModule\\App\\Libs\\Support\\TablePrefix', '\\TSSGGSModule\\App\\Libs\\Support\\ModuleRename', '\\TSSGGSModule\\App\\Database\\Schema'] as $step)
{
    try
    {
        call_user_func([$step, 'migrate']);
    }
    catch (\Throwable $e)
    {
        // the cron goes on with the names that are there
    }
}

$argList = $argv ?: ($_SERVER['argv'] ?: []);
if (count($argList) === 0)
{
    $argList = [__FILE__];
}

(new \TSSGGSModule\App\Kernel\Console\Runner())
    ->run();
