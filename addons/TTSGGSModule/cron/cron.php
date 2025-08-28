<?php

$modulePath = dirname(__DIR__);
$whmcsPath  = dirname(dirname(dirname($modulePath)));

require_once $whmcsPath . DIRECTORY_SEPARATOR . 'init.php';
require_once $modulePath . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Bootstrap.php';

//cause WHMCS
ini_set('max_execution_time', 0);

$argList = $argv ?: ($_SERVER['argv'] ?: []);
if (count($argList) === 0)
{
    $argList = [__FILE__];
}

(new \ModulesGarden\TTSGGSModule\Core\CommandLine\CronManager())
    ->run();
