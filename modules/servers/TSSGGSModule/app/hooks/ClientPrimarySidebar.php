<?php

namespace TSSGGSModule\Server\app\hooks;

use TSSGGSModule\Server\core\Lang;

/**
 * WHMCS hook ClientAreaPrimarySidebar (registered in hooks.php).
 */
class ClientPrimarySidebar
{
    public static function handle($primarySidebar)
    {


        if($_REQUEST['action'] == 'productdetails' && $_REQUEST['id'] > 0)
        {
            $serviceId = (int)$_REQUEST['id'];
            $orderData = \TSSGGSModule\App\Libs\Certificates\CertificateService::stored($serviceId);

            // a cancelled / terminated service keeps "active" in its stored order: no downloads or reissue for it
            $serviceOpen = \TSSGGSModule\App\Libs\Certificates\CertificateService::serviceOpen($serviceId);

            if($serviceOpen && isset($orderData['orderData']['order']['status']) && $orderData['orderData']['order']['status'] == 'active')
            {
                $myAccount = $primarySidebar->getChild('Service Details Actions');
                if($myAccount)
                {
                    // only the formats the admin offers (Settings > General > Certificate Downloads)
                    $formats = \TSSGGSModule\App\Libs\Certificates\CertificateBundle::enabledFormats();
                    if(in_array('crt', $formats, true))
                    {
                        $myAccount->addChild('DownloadCRT', [
                            'name'  => 'DownloadCRT',
                            'label' => Lang::absoluteT('Download CRT'),
                            'uri'   => 'clientarea.php?action=productdetails&id=' . $serviceId . '&mod-action=download&format=crt',
                            'order' => 10,
                            'icon'  => 'fas fa-cogs',
                        ]);
                    }
                    if(in_array('ca', $formats, true))
                    {
                        $myAccount->addChild('DownloadICRT', [
                            'name'  => 'DownloadICRT',
                            'label' => Lang::absoluteT('Download Intermediate CRT'),
                            'uri'   => 'clientarea.php?action=productdetails&id=' . $serviceId . '&mod-action=download&format=ca',
                            'order' => 20,
                            'icon'  => 'fas fa-cogs',
                        ]);
                    }
                    if(in_array('zip', $formats, true))
                    {
                        $myAccount->addChild('DownloadSSL', [
                            'name'  => 'DownloadSSL',
                            'label' => Lang::absoluteT('Download SSL'),
                            'uri'   => 'clientarea.php?action=productdetails&id=' . $serviceId . '&mod-action=download&format=zip',
                            'order' => 30,
                            'icon'  => 'fas fa-cogs',
                        ]);
                    }
                    $myAccount->addChild('ReissueSSL', [
                        'name'  => 'ReissueSSL',
                        'label' => Lang::absoluteT('Reissue SSL'),
                        'uri'   => 'clientarea.php?action=productdetails&id=' . $serviceId . '&mod-action=reissueSSL',
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

            // the validation actions while the vendor processes the order ("pending" is the same as "processing")
            if($serviceOpen && isset($orderData['orderData']['order']['status']) && in_array($orderData['orderData']['order']['status'], ['pending', 'processing'], true))
            {
                $myAccount = $primarySidebar->getChild('Service Details Actions');
                if($myAccount)
                {
                    $myAccount->addChild('DownloadCSR', [
                        'name'  => 'DownloadCSR',
                        'label' => Lang::absoluteT('Download CSR'),
                        'uri'   => 'clientarea.php?action=productdetails&id=' . $serviceId . '&mod-action=downloadCSR',
                        'order' => 10,
                        'icon'  => 'fas fa-cogs'
                    ]);
                    if(isset($orderData['orderData']['orderData']['common_name']['validation_method']) && ($orderData['orderData']['orderData']['common_name']['validation_method'] == 'http' || $orderData['orderData']['orderData']['common_name']['validation_method'] == 'https'))
                    {
                        $myAccount->addChild('DownloadAuthFile', [
                            'name'  => 'DownloadAuthFile',
                            'label' => Lang::absoluteT('Download Auth File'),
                            'uri'   => 'clientarea.php?action=productdetails&id=' . $serviceId . '&mod-action=downloadAuthFile',
                            'order' => 20,
                            'icon'  => 'fas fa-cogs'
                        ]);
                    }
                    $myAccount->addChild('ChangeApprovalMethod', [
                        'name'  => 'ChangeApprovalMethod',
                        'label' => Lang::absoluteT('Change Approval Method'),
                        'uri'   => 'clientarea.php?action=productdetails&id=' . $serviceId . '&mod-action=changeApprovalMethod',
                        'order' => 30,
                        'icon'  => 'fas fa-cogs'
                    ]);
                }
            }

        }
    }
}
