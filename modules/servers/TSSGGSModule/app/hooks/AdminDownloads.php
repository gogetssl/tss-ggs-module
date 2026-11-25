<?php

namespace TSSGGSModule\Server\app\hooks;

use WHMCS\Database\Capsule;
use TSSGGSModule\App\Models\Request;

/**
 * Admin downloads (certificate ZIP, validation file) from the module tab of the service page: a normal request, not
 * a WHMCS module button (those run through AJAX, where a file never reaches the browser). Admin session + CSRF token.
 *
 * WHMCS hook AdminAreaPage (registered in hooks.php).
 */
class AdminDownloads
{
    public static function handle($vars)
    {
        if(empty($_GET['tssggs_download']) || basename((string)$_SERVER['SCRIPT_NAME']) !== 'clientsservices.php' || empty($_SESSION['adminid']))
        {
            return;
        }
        $serviceId = (int)($_GET['id'] ?? 0);
        $module    = Capsule::table('tblhosting')->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->where('tblhosting.id', $serviceId)->value('tblproducts.servertype');
        if(!$serviceId || strtolower((string)$module) !== strtolower(basename(dirname(__DIR__, 2))))
        {
            return; // another module's service (both editions can be installed side by side)
        }
        \check_token('WHMCS.admin.default');
        \TSSGGSModule\Server\app\controllers\server\admin\Actions::adminDownload($serviceId, (string)$_GET['tssggs_download']);
    }
}
