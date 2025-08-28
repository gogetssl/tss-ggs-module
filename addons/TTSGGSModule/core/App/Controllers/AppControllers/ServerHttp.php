<?php

namespace ModulesGarden\TTSGGSModule\Core\App\Controllers\AppControllers;

use ModulesGarden\TTSGGSModule\Core\Contracts\Controllers\AppControllerInterface;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;

class ServerHttp extends \ModulesGarden\TTSGGSModule\Core\App\Controllers\AppController implements AppControllerInterface
{
    public function getControllerInstanceClass($callerName, $params)
    {
        $functionName = strtolower(str_replace(ModuleConstants::getModuleName() . '_', '', $callerName));

        switch ($functionName)
        {
            case 'clientarea':
                return $params['model'] instanceof \WHMCS\Service\Addon ?
                    \ModulesGarden\TTSGGSModule\Core\App\Controllers\Instances\Server\ClientPageProductAddonController::class :
                    \ModulesGarden\TTSGGSModule\Core\App\Controllers\Instances\Server\ClientPageController::class ;
            default:
                throw new \Exception($functionName . ' is not implemented in ' . __CLASS__);
        }

        return null;
    }
}
