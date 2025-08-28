<?php

use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Service;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use WHMCS\View\Menu\Item;

$hookManager->register(
    //Service Details Overview -> Information URL FIX
    function (Item $primarySidebar) {
        if (Request::get('action') != "productdetails")
        {
            return;
        }

        $service = Service::find(Request::get('id'));

        if (!$service->exists)
        {
            return;
        }

        //Prevent integrate other modules
        if ($service->product->servertype != ModuleConstants::getModuleName())
        {
            return;
        }

        $overview = $primarySidebar->getChild('Service Details Overview');

        if (!is_a($overview, '\WHMCS\View\Menu\Item'))
        {
            return;
        }

        $panel = $overview->getChild('Information');

        if (!is_a($panel, '\WHMCS\View\Menu\Item'))
        {
            return;
        }

        $panel->setUri("clientarea.php?action=productdetails&id={$service->id}");
        $panel->setAttributes([]);
    }, 1001);
