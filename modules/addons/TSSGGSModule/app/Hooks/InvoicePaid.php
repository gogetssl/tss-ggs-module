<?php


use ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates;
use ModulesGarden\TSSGGSModule\App\Libs\AcmeHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Service;

$hookManager->register(
    function($args) {
        $invoice = $args['invoice'];
        $items   = $invoice->items;

        foreach($items as $item)
        {
            if($item->type == 'Hosting' && str_contains($item->description, 'ACME Subscription Renewal'))
            {
                $serviceId = $item->relid;
                $service   = Service::find($serviceId);

                if($service && AcmeHelpers::isAcmeWhmcsProduct($service->packageid))
                {
                    EmailTemplates::sendEmail(EmailTemplates::SUBSCRIPTION_RENEWAL_TEMPLATE, $service->id, []);
                }
            }
        }
    },
    100
);
