<?php

namespace ModulesGarden\TSSGGSModule\App\Http\Admin;

use ModulesGarden\TSSGGSModule\App\Libs\AutoInstallQueue;
use ModulesGarden\TSSGGSModule\App\UI\Admin\CertificateLog\Index\CertificateLogContainer;
use ModulesGarden\TSSGGSModule\Core\Contracts\Controllers\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Helper;
use ModulesGarden\TSSGGSModule\Core\Http\AbstractController;
use WHMCS\Database\Capsule;

/**
 * Certificate event log: order, validation, reissue and installation events of all services, with clear
 */
class CertificateLog extends AbstractController implements AdminAreaInterface
{
    public function index()
    {
        return Helper\view()->addElement(CertificateLogContainer::class);
    }

    /** GET older=<days>|all with the admin link token */
    public function clear()
    {
        if(\ModulesGarden\TSSGGSModule\App\Libs\CsrfToken::valid($_REQUEST['token'] ?? ''))
        {
            AutoInstallQueue::createTables();
            $query = Capsule::table(AutoInstallQueue::LOGS_TABLE);
            $older = (string)($_REQUEST['older'] ?? '');
            // only "all" or a number of days: anything else deletes nothing
            if($older === 'all' || (ctype_digit($older) && (int)$older > 0))
            {
                if($older !== 'all')
                {
                    $query->where('created_at', '<', date('Y-m-d H:i:s', strtotime('-' . (int)$older . ' days')));
                }
                $_SESSION['tssggs_log_notice'] = $query->delete();
            }
        }

        return Helper\redirect('certificateLog', 'index');
    }
}
