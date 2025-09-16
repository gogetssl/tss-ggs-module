<?php

namespace ModulesGarden\TSSGGSModule\Core\App\Controllers\AppControllers;

use ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\AddonController;
use ModulesGarden\TSSGGSModule\Core\Contracts\Controllers\AppControllerInterface;
use ModulesGarden\TSSGGSModule\Core\ModuleConstants;

class Addon extends \ModulesGarden\TSSGGSModule\Core\App\Controllers\AppController implements AppControllerInterface
{
    public function getControllerInstanceClass($callerName, $params)
    {
        $functionName = str_replace(ModuleConstants::getModuleName() . '_', '', $callerName);


        $coreAddon = '\ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\Addon\\' . ucfirst($functionName);
        if (class_exists($coreAddon) && is_subclass_of($coreAddon, AddonController::class))
        {
            return $coreAddon;
        }

        $appAddon = '\ModulesGarden\TSSGGSModule\App\Http\Actions\\' . ucfirst($functionName);
        if (class_exists($appAddon) && is_subclass_of($appAddon, AddonController::class))
        {
            return $appAddon;
        }

        return null;
    }
}
