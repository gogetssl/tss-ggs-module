<?php

// only through WHMCS: a direct request gets nothing (no output, no error with the server path)
if(!defined('WHMCS'))
{
    http_response_code(403);
    exit;
}


use ModulesGarden\TSSGGSModule\Packages\Product\Support\Facades\Sidebar;

$hookManager->register(
    function($sidebarWhmcs) {

    },
    100
);

