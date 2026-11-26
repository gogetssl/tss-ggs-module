<?php

namespace TSSGGSModule\Server\app\hooks;

use WHMCS\Database\Capsule;

/**
 * WHMCS hook ClientAreaHeadOutput (registered in hooks.php).
 */
class AwaitingConfigurationNotice
{
    public static function handle($params)
    {

        // pages other than the client area lists have no clientareaaction
        if(in_array($params['clientareaaction'] ?? '', ['services', 'products'], true) && !empty($_SESSION['uid']))
        {

            $moduleName = basename(dirname(__DIR__, 2));
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
    }
}
