<?php

namespace ModulesGarden\TTSGGSModule\App\Http\Admin;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Statistics\Index\StatisticsContainer;
use ModulesGarden\TTSGGSModule\Core\Contracts\Controllers\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Http\AbstractController;
use ModulesGarden\TTSGGSModule\Core\Helper;
use function ModulesGarden\TTSGGSModule\Core\Helper;

class Statistics extends AbstractController implements AdminAreaInterface
{

    public function index()
    {
        return Helper\view()
            ->addElement(StatisticsContainer::class);
    }
}
