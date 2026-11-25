<?php

// only through WHMCS: a direct request gets nothing (no output, no error with the server path)
if(!defined('WHMCS'))
{
    http_response_code(403);
    exit;
}

// an update of the module that stopped half way (its process died) is undone here (App\Libs\Update\Swap)
$moduleUpdateJournal = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.' . strtolower(basename(__DIR__)) . '-update' . DIRECTORY_SEPARATOR . 'journal.json';
if(is_file($moduleUpdateJournal) && is_file(dirname($moduleUpdateJournal) . DIRECTORY_SEPARATOR . 'recover.php'))
{
    include dirname($moduleUpdateJournal) . DIRECTORY_SEPARATOR . 'recover.php';
}


use WHMCS\Database\Capsule;
use TSSGGSModule\App\Models\Request;
use TSSGGSModule\Server\core\Lang;

// Loaded by WHMCS (ModuleHooks) and by the addon's hooks.php - WHMCS lists the server module in ModuleHooks only
// after a product using it was saved in the admin area, so the addon makes sure the client hooks always run
if(defined('TSSGGSMODULE_SERVER_HOOKS'))
{
    return;
}
define('TSSGGSMODULE_SERVER_HOOKS', true);

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

require_once __DIR__ . DS . 'Loader.php';
new \TSSGGSModule\Server\Loader();
\TSSGGSModule\Server\Server::I();


// Admin downloads (certificate ZIP, validation file) from the module tab of the service page: a normal request, not
// a WHMCS module button (those run through AJAX, where a file never reaches the browser). Admin session + CSRF token.
add_hook('AdminAreaPage', 1, function($vars) { return \TSSGGSModule\Server\app\hooks\AdminDownloads::handle($vars); });

add_hook('ClientAreaPrimarySidebar', 1, function($primarySidebar) { return \TSSGGSModule\Server\app\hooks\ClientPrimarySidebar::handle($primarySidebar); });


add_hook('ClientAreaHeadOutput', 1, function($params) { return \TSSGGSModule\Server\app\hooks\AwaitingConfigurationNotice::handle($params); });

add_hook('ClientAreaPage', 1, function($params) { return \TSSGGSModule\Server\app\hooks\ClientAreaPage::handle($params); });

// Additional SANs cost their full price, not WHMCS's prorated one (App\Libs\Billing\UpgradePricing): the summary the client
// confirms, then the upgrade invoice
add_hook('ClientAreaPageUpgrade', 1, function($vars) { return \TSSGGSModule\Server\app\hooks\UpgradeSummary::handle($vars); });
// while WHMCS still builds the invoice (WHMCS 9 refuses changes to a finalised one)
add_hook('InvoiceCreation', 1, function($vars) { return \TSSGGSModule\Server\app\hooks\UpgradeInvoiceCreation::handle($vars); });
add_hook('InvoiceCreated', 1, function($vars) { return \TSSGGSModule\Server\app\hooks\UpgradeInvoiceCreated::handle($vars); });

// Paid SAN upgrade of an ordered certificate - buy the additional SANs at the gateway
add_hook('AfterConfigOptionsUpgrade', 1, function($vars) { return \TSSGGSModule\Server\app\hooks\SanUpgradePurchase::handle($vars); });

// SSL summary panel on the client area home page
add_hook('ClientAreaHomepagePanels', 1, function($homePagePanels) { return \TSSGGSModule\Server\app\hooks\HomepageSslPanel::handle($homePagePanels); });

// Both editions (GoGetSSL Module, TheSSLStore Plugin) can run on one WHMCS and each adds its own "SSL Certificates"
// block: once a second block appears, every block shows its brand so the client can tell them apart. Shared by
// the editions (same function name in both packages), hence the function_exists guard and the $GLOBALS list.
if(!function_exists('unifiedSslLabelBlock'))
{
    function unifiedSslLabelBlock($item, $title, $brand, $kind = 'sidebar')
    {
        $GLOBALS['unifiedSslBlocks'][$kind][] = [$item, $title, $brand];
        if(count($GLOBALS['unifiedSslBlocks'][$kind]) > 1)
        {
            foreach($GLOBALS['unifiedSslBlocks'][$kind] as list($block, $blockTitle, $blockBrand))
            {
                $block->setLabel($blockTitle . ' - ' . $blockBrand);
            }
        }
    }
}

// SSL summary in the secondary sidebar (on all pages, or on the pages listed in the settings)
add_hook('ClientAreaSecondarySidebar', 1, function($secondarySidebar) { return \TSSGGSModule\Server\app\hooks\ClientSecondarySidebar::handle($secondarySidebar); });

// ---- Client markups (Markups page): the client and currency of the visitor are in app/hooks/ClientCurrency ---- //

// Price used in the cart and on the invoice
add_hook('OrderProductPricingOverride', 1, function($vars) { return \TSSGGSModule\Server\app\hooks\CartPriceOverride::handle($vars); });

// Prices shown on the standard order form (product list and configure product)
add_hook('ClientAreaPageCart', 1, function($vars) { return \TSSGGSModule\Server\app\hooks\OrderFormPrices::handle($vars); });

// Lagom order form loads prices over AJAX - adjust them in the browser (the cart total comes from the override hook)
add_hook('ClientAreaHeadOutput', 1, function($vars) { return \TSSGGSModule\Server\app\hooks\LagomOrderFormPrices::handle($vars); });

// Orders of products set to "set up as soon as an order is placed" / "... when the first payment is received" stay
// Pending in WHMCS after the service was set up (a known WHMCS behaviour), so an admin has to accept each one. Once
// the certificate service was created, the order is accepted - only when nothing else in it still needs an admin
// (domains, addons, other services not set up yet); failed set-ups never get here.
add_hook('AfterModuleCreate', 1, function($vars) { return \TSSGGSModule\Server\app\hooks\AcceptOrderAfterSetup::handle($vars); });

// ---- Renewal invoices: the module creates them, never WHMCS (App\Libs\Billing\RenewalPolicy) ---- //
// WHMCS invoices a service when its next invoice date enters the generation window. Right before it generates
// invoices, the module's recurring services have that date moved out of the window; right after, it is put back.
// So a renewal is billed once, on the module's terms (N days before the order end, at most 30), and the dates the
// administrator sees do not change.
add_hook('PreInvoicingGenerateInvoiceItems', 1, function($vars) { return \TSSGGSModule\Server\app\hooks\RenewalInvoiceWindowBefore::handle($vars); });

add_hook('AfterInvoicingGenerateInvoiceItems', 1, function($vars) { return \TSSGGSModule\Server\app\hooks\RenewalInvoiceWindowAfter::handle($vars); });

/**
 * A product addon's certificate service (M-14): its page says which addon it is billed with, so the client does not
 * look for renewal or cancellation on the free service
 */
add_hook('ClientAreaProductDetailsOutput', 1, function($vars) { return \TSSGGSModule\Server\app\hooks\AddonCertificateNotice::handle($vars); });
