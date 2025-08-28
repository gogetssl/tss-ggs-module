<?php

use WHMCS\Database\Capsule;
use ModulesGarden\TTSGGSModule\App\Models\Request;
use ModulesGarden\Servers\TTSGGSModule\core\Lang;

if(!defined('DS'))define('DS',DIRECTORY_SEPARATOR);

require_once __DIR__.DS.'Loader.php';
new \ModulesGarden\Servers\TTSGGSModule\Loader();
\ModulesGarden\Servers\TTSGGSModule\Server::I();


add_hook('ClientAreaPrimarySidebar', 1, function($primarySidebar) {


    if($_REQUEST['action'] == 'productdetails' && $_REQUEST['id'] > 0)
    {
        $serviceId = $_REQUEST['id'];
        $orderData = Request::getOrder($serviceId);

        if(isset($orderData['orderData']['order']['status']) && $orderData['orderData']['order']['status'] == 'active') {
            $myAccount = $primarySidebar->getChild('Service Details Actions');
            if ($myAccount) {
                $myAccount->addChild('DownloadCRT', [
                    'name' => 'DownloadCRT',
                    'label' => Lang::absoluteT('Download CRT'),
                    'uri' => 'clientarea.php?action=productdetails&id=67&mg-action=downloadCRT',
                    'order' => 10,
                    'icon' => 'fas fa-cogs',
                ]);
                $myAccount->addChild('DownloadICRT', [
                    'name' => 'DownloadICRT',
                    'label' => Lang::absoluteT('Download Intermediate CRT'),
                    'uri' => 'clientarea.php?action=productdetails&id=67&mg-action=downloadICRT',
                    'order' => 20,
                    'icon' => 'fas fa-cogs',
                ]);
                $myAccount->addChild('DownloadSSL', [
                    'name' => 'DownloadSSL',
                    'label' => Lang::absoluteT('Download SSL'),
                    'uri' => 'clientarea.php?action=productdetails&id=67&mg-action=downloadSSL',
                    'order' => 30,
                    'icon' => 'fas fa-cogs',
                ]);
                $myAccount->addChild('ReissueSSL', [
                    'name' => 'ReissueSSL',
                    'label' => Lang::absoluteT('Reissue SSL'),
                    'uri' => 'clientarea.php?action=productdetails&id=67&mg-action=reissueSSL',
                    'order' => 40,
                    'icon' => 'fas fa-cogs',
                ]);
//                $myAccount->addChild('RenewSSL', [
//                    'name' => 'RenewSSL',
//                    'label' => Lang::absoluteT('Renew SSL'),
//                    'uri' => 'clientarea.php',
//                    'order' => 50,
//                    'icon' => 'fas fa-cogs',
//                ]);
                $myAccount->addChild('SendCert', [
                    'name' => 'SendCert',
                    'label' => Lang::absoluteT('Send Certificate'),
                    'uri' => 'clientarea.php?action=productdetails&id=67&mg-action=sendCertificate',
                    'order' => 60,
                    'icon' => 'fas fa-cogs',
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

        if(isset($orderData['orderData']['order']['status']) && $orderData['orderData']['order']['status'] == 'pending') {
            $myAccount = $primarySidebar->getChild('Service Details Actions');
            if ($myAccount) {
                $myAccount->addChild('DownloadCSR', [
                    'name' => 'DownloadCSR',
                    'label' => Lang::absoluteT('Download CSR'),
                    'uri' => 'clientarea.php?action=productdetails&id='.$serviceId.'&mg-action=downloadCSR',
                    'order' => 10,
                    'icon' => 'fas fa-cogs'
                ]);
                if(isset($orderData['orderData']['orderData']['common_name']['validation_method']) && ($orderData['orderData']['orderData']['common_name']['validation_method'] == 'http' || $orderData['orderData']['orderData']['common_name']['validation_method'] == 'https')) {
                    $myAccount->addChild('DownloadAuthFile', [
                        'name' => 'DownloadAuthFile',
                        'label' => Lang::absoluteT('Download Auth File'),
                        'uri' => 'clientarea.php?action=productdetails&id=' . $serviceId . '&mg-action=downloadAuthFile',
                        'order' => 20,
                        'icon' => 'fas fa-cogs'
                    ]);
                }
                $myAccount->addChild('ChangeApprovalMethod', [
                    'name' => 'ChangeApprovalMethod',
                    'label' => Lang::absoluteT('Change Approval Method'),
                    'uri' => 'clientarea.php?action=productdetails&id='.$serviceId.'&mg-action=changeApprovalMethod',
                    'order' => 30,
                    'icon' => 'fas fa-cogs'
                ]);
            }
        }

    }
});



add_hook('ClientAreaHeadOutput', 1, function($params) {

    if ($params['clientareaaction'] == 'services' || $params['clientareaaction'] == 'products') {

        $moduleName = (new \ModulesGarden\Servers\TTSGGSModule\Configuration())->systemName;
        $services = Capsule::table('tblhosting')
            ->select(['tblhosting.id'])
            ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->join('tblsslorders', 'tblsslorders.serviceid', '=', 'tblhosting.id')
            ->where('tblhosting.userid', $_SESSION['uid'])
            ->where('tblsslorders.status', 'Awaiting Configuration')
            ->where('tblproducts.servertype', $moduleName)
            ->get();

        $awaitingServicesSSLCENTER = [];
        foreach ($services as $service) {
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