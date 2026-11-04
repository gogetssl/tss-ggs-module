<?php

use WHMCS\Database\Capsule;
use ModulesGarden\TSSGGSModule\App\Models\Request;
use ModulesGarden\Servers\TSSGGSModule\core\Lang;

// Loaded by WHMCS (ModuleHooks) and by the addon's hooks.php - WHMCS lists the server module in ModuleHooks only
// after a product using it was saved in the admin area, so the addon makes sure the client hooks always run
if(defined('TSSGGSMODULE_SERVER_HOOKS'))
{
    return;
}
define('TSSGGSMODULE_SERVER_HOOKS', true);

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

require_once __DIR__ . DS . 'Loader.php';
new \ModulesGarden\Servers\TSSGGSModule\Loader();
\ModulesGarden\Servers\TSSGGSModule\Server::I();


add_hook('ClientAreaPrimarySidebar', 1, function($primarySidebar) {


    if($_REQUEST['action'] == 'productdetails' && $_REQUEST['id'] > 0)
    {
        $serviceId = (int)$_REQUEST['id'];
        $orderData = \ModulesGarden\TSSGGSModule\App\Libs\CertificateService::stored($serviceId);

        if(isset($orderData['orderData']['order']['status']) && $orderData['orderData']['order']['status'] == 'active')
        {
            $myAccount = $primarySidebar->getChild('Service Details Actions');
            if($myAccount)
            {
                // only the formats the admin offers (Settings > General > Certificate Downloads)
                $formats = \ModulesGarden\TSSGGSModule\App\Libs\CertificateBundle::enabledFormats();
                if(in_array('crt', $formats, true))
                {
                    $myAccount->addChild('DownloadCRT', [
                        'name'  => 'DownloadCRT',
                        'label' => Lang::absoluteT('Download CRT'),
                        'uri'   => 'clientarea.php?action=productdetails&id=' . $serviceId . '&mg-action=download&format=crt',
                        'order' => 10,
                        'icon'  => 'fas fa-cogs',
                    ]);
                }
                if(in_array('ca', $formats, true))
                {
                    $myAccount->addChild('DownloadICRT', [
                        'name'  => 'DownloadICRT',
                        'label' => Lang::absoluteT('Download Intermediate CRT'),
                        'uri'   => 'clientarea.php?action=productdetails&id=' . $serviceId . '&mg-action=download&format=ca',
                        'order' => 20,
                        'icon'  => 'fas fa-cogs',
                    ]);
                }
                if(in_array('zip', $formats, true))
                {
                    $myAccount->addChild('DownloadSSL', [
                        'name'  => 'DownloadSSL',
                        'label' => Lang::absoluteT('Download SSL'),
                        'uri'   => 'clientarea.php?action=productdetails&id=' . $serviceId . '&mg-action=download&format=zip',
                        'order' => 30,
                        'icon'  => 'fas fa-cogs',
                    ]);
                }
                $myAccount->addChild('ReissueSSL', [
                    'name'  => 'ReissueSSL',
                    'label' => Lang::absoluteT('Reissue SSL'),
                    'uri'   => 'clientarea.php?action=productdetails&id=' . $serviceId . '&mg-action=reissueSSL',
                    'order' => 40,
                    'icon'  => 'fas fa-cogs',
                ]);
//                $myAccount->addChild('RenewSSL', [
//                    'name' => 'RenewSSL',
//                    'label' => Lang::absoluteT('Renew SSL'),
//                    'uri' => 'clientarea.php',
//                    'order' => 50,
//                    'icon' => 'fas fa-cogs',
//                ]);
                // "Send Certificate" is a button in the main block of the service page (it needs a POST with a token, a
                // sidebar link can not carry one)

//                $myAccount->addChild('ExtraSANs', [
//                    'name' => 'ExtraSANs',
//                    'label' => Lang::absoluteT('Buy Extra SANs'),
//                    'uri' => 'clientarea.php',
//                    'order' => 70,
//                    'icon' => 'fas fa-cogs',
//                ]);
            }
        }

        if(isset($orderData['orderData']['order']['status']) && $orderData['orderData']['order']['status'] == 'pending')
        {
            $myAccount = $primarySidebar->getChild('Service Details Actions');
            if($myAccount)
            {
                $myAccount->addChild('DownloadCSR', [
                    'name'  => 'DownloadCSR',
                    'label' => Lang::absoluteT('Download CSR'),
                    'uri'   => 'clientarea.php?action=productdetails&id=' . $serviceId . '&mg-action=downloadCSR',
                    'order' => 10,
                    'icon'  => 'fas fa-cogs'
                ]);
                if(isset($orderData['orderData']['orderData']['common_name']['validation_method']) && ($orderData['orderData']['orderData']['common_name']['validation_method'] == 'http' || $orderData['orderData']['orderData']['common_name']['validation_method'] == 'https'))
                {
                    $myAccount->addChild('DownloadAuthFile', [
                        'name'  => 'DownloadAuthFile',
                        'label' => Lang::absoluteT('Download Auth File'),
                        'uri'   => 'clientarea.php?action=productdetails&id=' . $serviceId . '&mg-action=downloadAuthFile',
                        'order' => 20,
                        'icon'  => 'fas fa-cogs'
                    ]);
                }
                $myAccount->addChild('ChangeApprovalMethod', [
                    'name'  => 'ChangeApprovalMethod',
                    'label' => Lang::absoluteT('Change Approval Method'),
                    'uri'   => 'clientarea.php?action=productdetails&id=' . $serviceId . '&mg-action=changeApprovalMethod',
                    'order' => 30,
                    'icon'  => 'fas fa-cogs'
                ]);
            }
        }

    }
});


add_hook('ClientAreaHeadOutput', 1, function($params) {

    if($params['clientareaaction'] == 'services' || $params['clientareaaction'] == 'products')
    {

        $moduleName = (new \ModulesGarden\Servers\TSSGGSModule\Configuration())->systemName;
        $services   = Capsule::table('tblhosting')
                             ->select(['tblhosting.id'])
                             ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
                             ->join('tblsslorders', 'tblsslorders.serviceid', '=', 'tblhosting.id')
                             ->where('tblhosting.userid', $_SESSION['uid'])
                             ->where('tblsslorders.status', 'Awaiting Configuration')
                             ->where('tblproducts.servertype', $moduleName)
                             ->get();

        $awaitingServicesSSLCENTER = [];
        foreach($services as $service)
        {
            $awaitingServicesSSLCENTER[$service->id] = $service->id;
        }

        return '<script type="text/javascript">
        $(document).ready(function () {
        
            var awaitingServicesSSLCENTER = ' . json_encode($awaitingServicesSSLCENTER) . ';

            $("#tableServicesList tbody tr").each(function(index) {
                var serviceid = $(this).find("td:first-child").attr("data-element-id");
                
                if(awaitingServicesSSLCENTER[serviceid])
                {
                    $(this).find("td:nth-child(2)").append("<br><span class=\"label label-warning\">Awaiting Configuration</span>");
                }

            });
        });
    </script>';
    }
});

add_hook('ClientAreaPage', 1, function($params) {
    $page = basename($_SERVER['PHP_SELF']);

    if($page == 'upgrade.php' && isset($_GET['type']) && $_GET['type'] == 'configoptions')
    {
        $serviceId = (int)$_GET['id'];
        $service   = \ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Service::find($serviceId);

        if(!$service || $service->product->servertype != 'TSSGGSModule')
        {
            return [];
        }

        if(\ModulesGarden\TSSGGSModule\App\Libs\AcmeHelpers::isAcmeWhmcsProduct($service->product->id))
        {
            header('Location: clientarea.php?action=productdetails&id=' . $serviceId);
            exit;
        }

        // SANs of an ordered certificate can only be added
        if(isset($_POST['step']) && $_POST['step'] == '2' && isset($_POST['configoption']) && is_array($_POST['configoption']))
        {
            $error = \ModulesGarden\TSSGGSModule\App\Libs\SanUpgrade::downgradeError($serviceId, $_POST['configoption']);
            if($error !== '')
            {
                $_SESSION['tssggs_upgrade_error'] = $error;
                header('Location: upgrade.php?type=configoptions&id=' . $serviceId);
                exit;
            }
        }

        if(!empty($_SESSION['tssggs_upgrade_error']))
        {
            $error = $_SESSION['tssggs_upgrade_error'];
            unset($_SESSION['tssggs_upgrade_error']);

            return ['errormessage' => '<li>' . htmlspecialchars($error) . '</li>'];
        }
    }

    return [];
});

// Paid SAN upgrade of an ordered certificate - buy the additional SANs at the gateway
add_hook('AfterConfigOptionsUpgrade', 1, function($vars) {
    try
    {
        \ModulesGarden\TSSGGSModule\App\Libs\SanUpgrade::afterUpgrade(isset($vars['upgradeid']) ? (int)$vars['upgradeid'] : 0);
    }
    catch(\Throwable $e)
    {
        logActivity('TSSGGSModule: SAN upgrade failed - ' . $e->getMessage());
    }
});

// SSL summary panel on the client area home page
add_hook('ClientAreaHomepagePanels', 1, function($homePagePanels) {
    try
    {
        if(empty($_SESSION['uid']) || !\ModulesGarden\TSSGGSModule\App\Libs\GeneralSettings::enabled('clientSummary'))
        {
            return;
        }

        $counts = (new \ModulesGarden\TSSGGSModule\App\Libs\ClientSslSummary($_SESSION['uid']))->counts();
        if(!$counts['total'])
        {
            return;
        }

        $t     = \ModulesGarden\TSSGGSModule\App\ClientArea\SslPages::texts();
        $icons = ['unpaid' => 'fa-credit-card', 'processing' => 'fa-cogs', 'expires_soon' => 'fa-hourglass-half'];
        $body  = '<div class="tssggs-summary" style="display:flex;flex-wrap:wrap;text-align:center;">';
        foreach($icons as $type => $icon)
        {
            $body .= '<a href="index.php?m=TSSGGSModule&amp;type=' . $type . '" style="flex:1 1 33%;padding:15px 5px;text-decoration:none;">'
                . '<i class="fas ' . $icon . '" style="font-size:22px;display:block;margin-bottom:6px;"></i>'
                . '<span style="display:block;">' . $t[$type] . '</span><strong style="font-size:20px;">' . (int)$counts[$type] . '</strong></a>';
        }
        $body .= '</div>';

        $homePagePanels->addChild('tssggs_ssl_summary', [
            'label'    => $t['summaryTitle'],
            'icon'     => 'fa-lock',
            'order'    => 60,
            'extras'   => [
                'color'    => 'gold',
                'btn-link' => 'index.php?m=TSSGGSModule&type=total',
                'btn-text' => $t['viewAll'],
                'btn-icon' => 'fas fa-arrow-right',
            ],
            'bodyHtml' => $body,
        ]);
    }
    catch(\Throwable $e)
    {
        logActivity('TSSGGSModule SSL summary panel: ' . $e->getMessage());
    }
});

// SSL summary in the secondary sidebar (on all pages, or on the pages listed in the settings)
add_hook('ClientAreaSecondarySidebar', 1, function($secondarySidebar) {
    try
    {
        if(empty($_SESSION['uid']) || !\ModulesGarden\TSSGGSModule\App\Libs\GeneralSettings::enabled('clientSummary'))
        {
            return;
        }

        global $smarty;
        $template = is_object($smarty) && isset($smarty->tpl_vars['templatefile']) ? (string)$smarty->tpl_vars['templatefile']->value : '';
        $allowed  = array_filter(array_map('trim', explode(',', (string)\ModulesGarden\TSSGGSModule\App\Libs\GeneralSettings::get('sidebarTemplates'))));
        if($allowed ? !in_array($template, $allowed, true) : $template === 'clientareahome')
        {
            return;
        }

        $counts = (new \ModulesGarden\TSSGGSModule\App\Libs\ClientSslSummary($_SESSION['uid']))->counts();
        if(!$counts['total'])
        {
            return;
        }

        $t    = \ModulesGarden\TSSGGSModule\App\ClientArea\SslPages::texts();
        $menu = $secondarySidebar->addChild('tssggsSslSummary', [
            'label' => $t['summaryTitle'],
            'icon'  => 'fa-lock',
            'order' => 99,
        ]);
        $order = 10;
        foreach(\ModulesGarden\TSSGGSModule\App\Libs\ClientSslSummary::TYPES as $type)
        {
            $menu->addChild('tssggsSsl_' . $type, [
                'label' => $t[$type],
                'uri'   => 'index.php?m=TSSGGSModule&type=' . $type,
                'badge' => (int)$counts[$type],
                'order' => $order++,
            ]);
        }
        if(\ModulesGarden\TSSGGSModule\App\Libs\GeneralSettings::enabled('certificatesByDomain'))
        {
            $menu->addChild('tssggsSsl_domains', [
                'label' => $t['byDomain'],
                'uri'   => 'index.php?m=TSSGGSModule&page=domains',
                'order' => $order++,
            ]);
        }
        if(\ModulesGarden\TSSGGSModule\App\Libs\GeneralSettings::enabled('sslTools'))
        {
            $menu->addChild('tssggsSsl_tools', [
                'label' => 'SSL Tools',
                'uri'   => 'index.php?m=TSSGGSModule&page=tools',
                'order' => $order,
            ]);
        }
    }
    catch(\Throwable $e)
    {
    }
});

// ---- Per-client commissions (Commissions page) ---- //

function tssggs_commission_client_currency()
{
    $clientId = !empty($_SESSION['uid']) ? (int)$_SESSION['uid'] : 0;
    if($clientId && function_exists('getCurrency'))
    {
        $currency = getCurrency($clientId);

        return [$clientId, (int)$currency['id']];
    }
    $currencyId = !empty($_SESSION['currency']) ? (int)$_SESSION['currency'] : (int)Capsule::table('tblcurrencies')->where('default', 1)->value('id');

    return [0, $currencyId];
}

// Price used in the cart and on the invoice
add_hook('OrderProductPricingOverride', 1, function($vars) {
    try
    {
        list($clientId, $currencyId) = tssggs_commission_client_currency();
        $price = \ModulesGarden\TSSGGSModule\App\Libs\Commissions::price($clientId, (int)$vars['pid'], $vars['proddata']['billingcycle'] ?? 'annually', $currencyId);

        return $price === null ? [] : ['recurring' => $price];
    }
    catch(\Throwable $e)
    {
        return [];
    }
});

// Prices shown on the standard order form (product list and configure product)
add_hook('ClientAreaPageCart', 1, function($vars) {
    try
    {
        list($clientId, $currencyId) = tssggs_commission_client_currency();
        $scale = function($pricing, $pid) use ($clientId, $currencyId) {
            $commission = \ModulesGarden\TSSGGSModule\App\Libs\Commissions::forProduct($clientId, $pid);
            if($commission === null || !is_array($pricing) || !Capsule::table('tblproducts')->where('id', (int)$pid)->where('servertype', 'TSSGGSModule')->exists())
            {
                return $pricing;
            }
            foreach((array)($pricing['rawpricing'] ?? []) as $cycle => $amount)
            {
                if(!is_numeric($amount) || (float)$amount <= 0)
                {
                    continue;
                }
                $new = \ModulesGarden\TSSGGSModule\App\Libs\Commissions::apply($amount, $commission);
                $pricing['rawpricing'][$cycle] = number_format($new, 2, '.', '');
                if(isset($pricing['cycles'][$cycle]))
                {
                    $pricing['cycles'][$cycle] = str_replace((string)formatCurrency($amount, $currencyId), (string)formatCurrency($new, $currencyId), (string)$pricing['cycles'][$cycle]);
                }
            }
            if(isset($pricing['minprice']['price']) && !empty($pricing['minprice']['cycle']) && isset($pricing['rawpricing'][$pricing['minprice']['cycle']]))
            {
                $pricing['minprice']['price'] = formatCurrency($pricing['rawpricing'][$pricing['minprice']['cycle']], $currencyId);
            }

            return $pricing;
        };

        $return = [];
        if(!empty($vars['products']) && is_array($vars['products']))
        {
            $products = $vars['products'];
            foreach($products as $key => $product)
            {
                if(isset($product['pid'], $product['pricing']))
                {
                    $products[$key]['pricing'] = $scale($product['pricing'], (int)$product['pid']);
                }
            }
            $return['products'] = $products;
        }
        if(!empty($vars['pricing']) && !empty($vars['productinfo']['pid']))
        {
            $return['pricing'] = $scale($vars['pricing'], (int)$vars['productinfo']['pid']);
        }

        return $return;
    }
    catch(\Throwable $e)
    {
        return [];
    }
});

// Lagom order form loads prices over AJAX - adjust them in the browser (the cart total comes from the override hook)
add_hook('ClientAreaHeadOutput', 1, function($vars) {
    if(strpos((string)($vars['template'] ?? ''), 'lagom') === false)
    {
        return '';
    }
    try
    {
        list($clientId) = tssggs_commission_client_currency();
        $commissions = [];
        foreach(Capsule::table('tblproducts')->where('servertype', 'TSSGGSModule')->pluck('id') as $pid)
        {
            $commission = \ModulesGarden\TSSGGSModule\App\Libs\Commissions::forProduct($clientId, $pid);
            if($commission !== null)
            {
                $commissions[(int)$pid] = 1 + $commission / 100;
            }
        }
        if(!$commissions)
        {
            return '';
        }
    }
    catch(\Throwable $e)
    {
        return '';
    }

    return '<script>(function () {
    var factors = ' . json_encode($commissions) . ', months = {monthly: 1, quarterly: 3, semiannually: 6, annually: 12, biennially: 24, triennially: 36};
    function scale(pricing, factor, prefix) {
        Object.keys(pricing || {}).forEach(function (cycle) {
            var p = pricing[cycle], raw = parseFloat(p && p.rawPrice);
            if (isNaN(raw) || raw <= 0) { return; }
            var value = (raw * factor).toFixed(2);
            p.rawPrice = value;
            p.price = p.finalPrice = p.startingPrice = prefix + value;
            p.monthlyPrice = prefix + (value / (months[cycle] || 1)).toFixed(2);
        });
    }
    var open = XMLHttpRequest.prototype.open;
    XMLHttpRequest.prototype.open = function (method, url) {
        if (url && String(url).indexOf("LagomOrderForm") !== -1) {
            var xhr = this;
            xhr.addEventListener("readystatechange", function () {
                if (xhr.readyState !== 4) { return; }
                try {
                    var data = JSON.parse(xhr.responseText), changed = false;
                    var prefix = data.settings && data.settings.currency ? data.settings.currency.prefix : "";
                    ((data.appState && data.appState.groups) || []).forEach(function (group) {
                        (group.products || []).forEach(function (product) {
                            var factor = factors[product.id];
                            if (!factor) { return; }
                            changed = true;
                            scale(product.pricing, factor, prefix);
                            Object.keys(product.pricings || {}).forEach(function (id) { scale(product.pricings[id], factor, prefix); });
                        });
                    });
                    if (changed) {
                        var text = JSON.stringify(data);
                        Object.defineProperty(xhr, "responseText", {get: function () { return text; }});
                        Object.defineProperty(xhr, "response", {get: function () { return text; }});
                    }
                } catch (e) {}
            });
        }
        return open.apply(this, arguments);
    };
})();</script>';
});

// Orders of products set to "set up as soon as an order is placed" / "... when the first payment is received" stay
// Pending in WHMCS after the service was set up (a known WHMCS behaviour), so an admin has to accept each one. Once
// the certificate service was created, the order is accepted - only when nothing else in it still needs an admin
// (domains, addons, other services not set up yet); failed set-ups never get here.
add_hook('AfterModuleCreate', 1, function($vars) {
    try
    {
        $params    = isset($vars['params']) && is_array($vars['params']) ? $vars['params'] : [];
        $serviceId = (int)($params['serviceid'] ?? 0);
        if(!$serviceId || ($params['moduletype'] ?? 'TSSGGSModule') !== 'TSSGGSModule')
        {
            return;
        }

        $service = Capsule::table('tblhosting as h')->join('tblproducts as p', 'p.id', '=', 'h.packageid')
            ->where('h.id', $serviceId)->first(['h.orderid', 'p.autosetup', 'p.servertype']);
        if(!$service || $service->servertype !== 'TSSGGSModule' || !in_array($service->autosetup, ['order', 'payment'], true) || !(int)$service->orderid)
        {
            return;
        }

        $orderId = (int)$service->orderid;
        if(Capsule::table('tblorders')->where('id', $orderId)->value('status') !== 'Pending')
        {
            return;
        }

        // everything else in the order is done: no domains or addons waiting, every other service already set up
        $waiting = Capsule::table('tblhosting')->where('orderid', $orderId)->where('id', '!=', $serviceId)->where('domainstatus', 'Pending')->exists()
            || Capsule::table('tbldomains')->where('orderid', $orderId)->where('status', 'Pending')->exists()
            || Capsule::table('tblhostingaddons')->where('orderid', $orderId)->where('status', 'Pending')->exists();
        if($waiting)
        {
            return;
        }

        $result = localAPI('AcceptOrder', ['orderid' => $orderId, 'autosetup' => false, 'sendemail' => false]);
        if(($result['result'] ?? '') === 'success')
        {
            \ModulesGarden\TSSGGSModule\App\Libs\GeneralSettings::activity('Order #' . $orderId . ' accepted automatically: service #' . $serviceId . ' was set up (automatic setup on ' . $service->autosetup . ')');
        }
        else
        {
            \ModulesGarden\TSSGGSModule\App\Libs\GeneralSettings::activity('Order #' . $orderId . ' could not be accepted automatically: ' . ($result['message'] ?? 'unknown error'));
        }
    }
    catch(\Throwable $e)
    {
        logActivity('TSSGGSModule order auto-accept: ' . $e->getMessage());
    }
});
