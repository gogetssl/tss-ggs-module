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
require_once $modulePath . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Bootstrap.php';

//cause WHMCS
ini_set('max_execution_time', 0);

// an install upgraded by copying files, where WHMCS never ran the addon upgrade, is still moved over
foreach (['TablePrefix', 'ModuleRename'] as $step)
{
    try
    {
        call_user_func(['\\ModulesGarden\\TSSGGSModule\\App\\Libs\\' . $step, 'migrate']);
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

(new \ModulesGarden\TSSGGSModule\Core\CommandLine\CronManager())
    ->run();
