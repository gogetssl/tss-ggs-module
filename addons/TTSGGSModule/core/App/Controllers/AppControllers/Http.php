<?php

namespace ModulesGarden\TTSGGSModule\Core\App\Controllers\AppControllers;

use ModulesGarden\TTSGGSModule\Core\App\Controllers\Instances\Http\AdminPageController;
use ModulesGarden\TTSGGSModule\Core\App\Controllers\Instances\Http\ClientPageController;
use ModulesGarden\TTSGGSModule\Core\Contracts\Controllers\AppControllerInterface;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;

class Http extends \ModulesGarden\TTSGGSModule\Core\App\Controllers\AppController implements AppControllerInterface
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
