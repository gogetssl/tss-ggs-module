<?php

namespace ModulesGarden\TTSGGSModule\App\Http\Client;

use ModulesGarden\TTSGGSModule\Core\Helper;
use ModulesGarden\TTSGGSModule\Core\Http\AbstractClientController;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Pages\LogsDataTable;

/**
 * Description of Samples
 */
class Home extends AbstractClientController implements \ModulesGarden\TTSGGSModule\Core\Contracts\Controllers\ClientAreaInterface
{
    public function index()
    {
        return Helper\view()
            ->addElement(\ModulesGarden\TTSGGSModule\App\UI\Client\Home\Index\Container::class);
    }

    public function serviceInformation()
    {
        return Helper\view()
            ->addElement(\ModulesGarden\TTSGGSModule\App\UI\Client\Home\ServiceInformation\Container::class);
    }
}
