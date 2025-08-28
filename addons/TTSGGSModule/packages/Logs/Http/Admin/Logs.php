<?php

namespace ModulesGarden\TTSGGSModule\Packages\Logs\Http\Admin;

use ModulesGarden\TTSGGSModule\Core\Contracts\Controllers\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Helper;
use ModulesGarden\TTSGGSModule\Core\Http\AbstractController;
use ModulesGarden\TTSGGSModule\Core\UI\View;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Pages\LogsDataTable;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Widgets\Summary;

class Logs extends AbstractController implements AdminAreaInterface
{
    /**
     * Example of static page
     * @return View
     */
    public function index()
    {
        return Helper\view()
            ->addElement(Summary::class)
            ->addElement(LogsDataTable::class);
    }
}
