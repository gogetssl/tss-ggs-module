<?php

namespace ModulesGarden\TTSGGSModule\Core\App\Controllers\AppControllers;

use ModulesGarden\TTSGGSModule\Core\App\Controllers\Instances\AddonController;
use ModulesGarden\TTSGGSModule\Core\Contracts\Controllers\AppControllerInterface;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;

class Addon extends \ModulesGarden\TTSGGSModule\Core\App\Controllers\AppController implements AppControllerInterface
{
    public function getControllerInstanceClass($callerName, $params)
    {
        $functionName = str_replace(ModuleConstants::getModuleName() . '_', '', $callerName);


        $coreAddon = '\ModulesGarden\TTSGGSModule\Core\App\Controllers\Instances\Addon\\' . ucfirst($functionName);
        if (class_exists($coreAddon) && is_subclass_of($coreAddon, AddonController::class))
        {
            return $coreAddon;
        }

        $appAddon = '\ModulesGarden\TTSGGSModule\App\Http\Actions\\' . ucfirst($functionName);
        if (class_exists($appAddon) && is_subclass_of($appAddon, AddonController::class))
        {
            return $appAddon;
        }

        return null;
    }
}
