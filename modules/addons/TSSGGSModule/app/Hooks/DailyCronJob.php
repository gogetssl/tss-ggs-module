<?php

// only through WHMCS: a direct request gets nothing (no output, no error with the server path)
if(!defined('WHMCS'))
{
    http_response_code(403);
    exit;
}


use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;


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

// the AutoInstall report e-mail to the administrators (App\Libs\AutoInstallReport), once a day
$hookManager->register(
    function($args) {
        try
        {
            \ModulesGarden\TSSGGSModule\App\Libs\AutoInstallReport::notify();
        }
        catch(\Throwable $e)
        {
            logActivity('SSL module: the AutoInstall report could not be sent: ' . $e->getMessage());
        }
    },
    110
);
