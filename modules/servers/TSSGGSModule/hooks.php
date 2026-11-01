<?php

use WHMCS\Database\Capsule;
use ModulesGarden\TSSGGSModule\App\Models\Request;
use ModulesGarden\Servers\TSSGGSModule\core\Lang;

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
                $myAccount->addChild('DownloadCRT', [
                    'name'  => 'DownloadCRT',
                    'label' => Lang::absoluteT('Download CRT'),
                    'uri'   => 'clientarea.php?action=productdetails&id=' . $serviceId . '&mg-action=download&format=crt',
                    'order' => 10,
                    'icon'  => 'fas fa-cogs',
                ]);
                $myAccount->addChild('DownloadICRT', [
                    'name'  => 'DownloadICRT',
                    'label' => Lang::absoluteT('Download Intermediate CRT'),
                    'uri'   => 'clientarea.php?action=productdetails&id=' . $serviceId . '&mg-action=download&format=ca',
                    'order' => 20,
                    'icon'  => 'fas fa-cogs',
                ]);
                $myAccount->addChild('DownloadSSL', [
                    'name'  => 'DownloadSSL',
                    'label' => Lang::absoluteT('Download SSL'),
                    'uri'   => 'clientarea.php?action=productdetails&id=' . $serviceId . '&mg-action=download&format=zip',
                    'order' => 30,
                    'icon'  => 'fas fa-cogs',
                ]);
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
                $myAccount->addChild('SendCert', [
                    'name'  => 'SendCert',
                    'label' => Lang::absoluteT('Send Certificate'),
                    'uri'   => 'clientarea.php?action=productdetails&id=' . $serviceId . '&mg-action=sendCertificate',
                    'order' => 60,
                    'icon'  => 'fas fa-cogs',
                ]);

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
                'order' => $order,
            ]);
        }
    }
    catch(\Throwable $e)
    {
    }
});
