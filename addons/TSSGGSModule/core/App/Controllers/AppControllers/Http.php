<?php

namespace ModulesGarden\TSSGGSModule\Core\App\Controllers\AppControllers;

use ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\Http\AdminPageController;
use ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\Http\ClientPageController;
use ModulesGarden\TSSGGSModule\Core\Contracts\Controllers\AppControllerInterface;
use ModulesGarden\TSSGGSModule\Core\ModuleConstants;

class Http extends \ModulesGarden\TSSGGSModule\Core\App\Controllers\AppController implements AppControllerInterface
{
    public function getControllerInstanceClass($callerName, $params)
    {
        $functionName = strtolower(str_replace(ModuleConstants::getModuleName() . '_', '', $callerName));
        switch ($functionName)
        {
            //HTTP controllers
            case 'output':
                return AdminPageController::class;
            case 'clientarea':
                return ClientPageController::class;
        }

        return null;
    }
}
