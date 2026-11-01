<?php

use ModulesGarden\TSSGGSModule\App\Libs\CronLauncher;

// WHMCS's system cron starts the module's jobs when the module's own cron entry is not in use (see CronLauncher)
$hookManager->register(
    function($args) {
        CronLauncher::launch();
    },
    100
);
