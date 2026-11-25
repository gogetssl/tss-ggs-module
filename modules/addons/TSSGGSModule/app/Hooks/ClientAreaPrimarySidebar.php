<?php

// only through WHMCS: a direct request gets nothing (no output, no error with the server path)
if(!defined('WHMCS'))
{
    http_response_code(403);
    exit;
}

use ModulesGarden\TSSGGSModule\App\Kernel\Kernel;
use WHMCS\Database\Capsule;

// Service page of one of the module's products: "Information" in the sidebar opens the service page itself
// (WHMCS links it to a tab the module's own service page does not have)
$hookManager->register(
    function($primarySidebar) {
        if(($_REQUEST['action'] ?? '') !== 'productdetails' || !is_object($primarySidebar))
        {
            return;
        }
        $serviceId  = (int)($_REQUEST['id'] ?? 0);
        $serverType = $serviceId ? Capsule::table('tblhosting as h')->join('tblproducts as p', 'p.id', '=', 'h.packageid')->where('h.id', $serviceId)->value('p.servertype') : null;
        if($serverType !== Kernel::module())
        {
            return;
        }
        $overview = $primarySidebar->getChild('Service Details Overview');
        $panel    = is_object($overview) ? $overview->getChild('Information') : null;
        if(is_object($panel))
        {
            $panel->setUri('clientarea.php?action=productdetails&id=' . $serviceId);
            $panel->setAttributes([]);
        }
    },
    1001
);
