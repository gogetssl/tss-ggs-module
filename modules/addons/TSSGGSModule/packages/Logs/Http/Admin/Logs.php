<?php

namespace ModulesGarden\TSSGGSModule\Packages\Logs\Http\Admin;

use ModulesGarden\TSSGGSModule\Core\Contracts\Controllers\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Helper;
use ModulesGarden\TSSGGSModule\Core\Http\AbstractController;
use ModulesGarden\TSSGGSModule\Core\UI\View;
use ModulesGarden\TSSGGSModule\Packages\Logs\UI\Pages\LogsDataTable;
use ModulesGarden\TSSGGSModule\Packages\Logs\UI\Widgets\Summary;

class Logs extends AbstractController implements AdminAreaInterface
{
    /**
     * Example of static page
     * @return View
     */
    /** Ends debug mode before its time runs out (link with the admin token) */
    public function stopDebug()
    {
        if(\ModulesGarden\TSSGGSModule\App\Libs\CsrfToken::valid($_REQUEST['token'] ?? ''))
        {
            \ModulesGarden\TSSGGSModule\App\Libs\GeneralSettings::save(['debugServices' => '', 'debugUntil' => 0]);
        }

        return Helper\redirect('logs', 'index');
    }

    public function index()
    {
        return Helper\view()
            ->addElement(Summary::class)
            ->addElement(LogsDataTable::class);
    }
}
