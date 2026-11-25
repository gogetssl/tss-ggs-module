<?php

// only through WHMCS: a direct request gets nothing (no output, no error with the server path)
if(!defined('WHMCS'))
{
    http_response_code(403);
    exit;
}


use TSSGGSModule\App\Libs\Support\CronLauncher;

// WHMCS's system cron starts the module's jobs when the module's own cron entry is not in use (see CronLauncher)
$hookManager->register(
    function($args) {
        CronLauncher::launch();
    },
    100
);
