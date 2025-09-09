<?php

namespace ModulesGarden\TSSGGSModule\App\Http\Client;

use ModulesGarden\TSSGGSModule\Core\Helper;
use ModulesGarden\TSSGGSModule\Core\Http\AbstractClientController;
use ModulesGarden\TSSGGSModule\Packages\Logs\UI\Pages\LogsDataTable;

/**
 * Description of Samples
 */
class Home extends AbstractClientController implements \ModulesGarden\TSSGGSModule\Core\Contracts\Controllers\ClientAreaInterface
{
    public function index()
    {
        return Helper\view()
            ->addElement(\ModulesGarden\TSSGGSModule\App\UI\Client\Home\Index\Container::class);
    }

    public function serviceInformation()
    {
        return Helper\view()
            ->addElement(\ModulesGarden\TSSGGSModule\App\UI\Client\Home\ServiceInformation\Container::class);
    }
}
