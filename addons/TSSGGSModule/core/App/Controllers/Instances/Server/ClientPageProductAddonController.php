<?php

namespace ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\Server;

use ModulesGarden\TSSGGSModule\Core\Contracts\Controllers\ClientAreaInterface;
use ModulesGarden\TSSGGSModule\Core\ModuleConstants;

class ClientPageProductAddonController extends ClientPageController implements ClientAreaInterface
{
    public function execute($params = null)
    {
        return \ModulesGarden\TSSGGSModule\Core\Support\Facades\Smarty::view('clientarea', parent::run($params), ModuleConstants::getTemplateDir() . '/controllers');
    }
}