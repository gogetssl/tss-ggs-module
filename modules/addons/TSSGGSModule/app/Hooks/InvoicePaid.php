<?php


use ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates;
use ModulesGarden\TSSGGSModule\App\Libs\AcmeHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Service;

$hookManager->register(
    function($args) {
        // WHMCS passes the invoice ID only; a failure here must never break the payment
        try
        {
            $items = \WHMCS\Database\Capsule::table('tblinvoiceitems')
                ->where('invoiceid', (int)($args['invoiceid'] ?? 0))
                ->where('type', 'Hosting')
                ->where('description', 'like', '%' . AcmeHelpers::RENEWAL_LABEL . '%')
                ->get(['relid']);

            foreach($items as $item)
            {
                $service = Service::find((int)$item->relid);

                if($service && \ModulesGarden\TSSGGSModule\App\Libs\SubscriptionBilling::isSubscriptionProduct($service->packageid))
                {
                    EmailTemplates::sendEmail(EmailTemplates::SUBSCRIPTION_RENEWAL_TEMPLATE, $service->id, []);
                }
            }
        }
        catch(\Throwable $e)
        {
            logActivity('SSL module: the ACME subscription renewal e-mail could not be sent: ' . $e->getMessage());
        }
    },
    100
);
