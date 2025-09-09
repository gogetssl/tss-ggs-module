<?php

namespace ModulesGarden\TSSGGSModule\App\Http\Admin;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Statistics\Index\StatisticsContainer;
use ModulesGarden\TSSGGSModule\Core\Contracts\Controllers\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Http\AbstractController;
use ModulesGarden\TSSGGSModule\Core\Helper;
use function ModulesGarden\TSSGGSModule\Core\Helper;

class Statistics extends AbstractController implements AdminAreaInterface
{

    public function index()
    {
        return Helper\view()
            ->addElement(StatisticsContainer::class);
    }
}
