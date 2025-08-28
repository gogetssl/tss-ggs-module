<?php

namespace ModulesGarden\TTSGGSModule\Core\App\Controllers\Instances\Server;

use ModulesGarden\TTSGGSModule\Core\Contracts\Controllers\ClientAreaInterface;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;

class ClientPageProductAddonController extends ClientPageController implements ClientAreaInterface
{
    public function execute($params = null)
    {
        return \ModulesGarden\TTSGGSModule\Core\Support\Facades\Smarty::view('clientarea', parent::run($params), ModuleConstants::getTemplateDir() . '/controllers');
    }
}