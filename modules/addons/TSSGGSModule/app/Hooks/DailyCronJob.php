<?php

use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TSSGGSModule\Core\Hook\HookIntegrator;


$hookManager->register(
    function($args) {
        // the gateway being down must not stop the daily cron of WHMCS
        try
        {
            RemoteProduct::synchronize();
        }
        catch(\Throwable $e)
        {
            logActivity('SSL module: the product list could not be refreshed: ' . $e->getMessage());
        }
    },
    100
);
