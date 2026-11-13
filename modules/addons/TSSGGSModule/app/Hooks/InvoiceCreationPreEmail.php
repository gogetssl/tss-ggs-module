<?php

use ModulesGarden\TSSGGSModule\App\Libs\SubscriptionBilling;

/**
 * InvoiceCreationPreEmail: the same pricing as InvoiceCreated, before the invoice e-mail goes out (idempotent, whichever runs first wins).
 *
 * ACME domain slots bought by an upgrade: priced by the subscription rules (prorated to the renewal, or the yearly
 * price), not at the one-time option price WHMCS charges. Covers the module's "Buy more domains" and the WHMCS upgrade page.
 */
$hookManager->register(
    function($args) {
        try
        {
            SubscriptionBilling::priceSlotUpgradeInvoice((int)($args['invoiceid'] ?? 0));
        }
        catch(\Throwable $e)
        {
            logActivity('SSL module: the domain slots of invoice #' . (int)($args['invoiceid'] ?? 0) . ' could not be priced: ' . $e->getMessage());
        }
    },
    100
);
