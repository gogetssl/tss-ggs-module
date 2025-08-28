<?php

namespace ModulesGarden\TTSGGSModule\Core\App;

use ModulesGarden\TTSGGSModule\Core\App\Controllers\AppControllers\Addon;
use ModulesGarden\TTSGGSModule\Core\App\Controllers\AppControllers\Api;
use ModulesGarden\TTSGGSModule\Core\App\Controllers\AppControllers\Http;
use ModulesGarden\TTSGGSModule\Core\App\Controllers\AppControllers\MetaData;
use ModulesGarden\TTSGGSModule\Core\App\Controllers\AppControllers\ServerHttp;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\ServiceLocator;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Params;

class Application
{
    public function run(string $type, string $callerName, $params = null)
    {
        try
        {

            $controller = $this->getControllerClass($type, $callerName);

            $controllerInstance = new $controller((array)$params);

            $result = $controllerInstance->runController($callerName, $params);

            return $result;
        }
        catch (\Throwable $exc)
        {
            $params['mgErrorDetails'] = $exc;
            $params['exception']      = $exc;

            $result = ServiceLocator::create(Controllers\Instances\Http\ErrorPage::class)->execute($params);


            return $result;
        }
    }

    public function getControllerClass(string $type, string $callerName = null)
    {
        $functionName = strtolower(str_replace(ModuleConstants::getModuleName() . '_', '', $callerName));

        switch ($type . '.' . $functionName)
        {
            //HTTP controllers
            case 'server.output':
            case 'server.clientarea':
                return ServerHttp::class;
            case 'addon.output':
            case 'addon.clientarea':
                return Http::class;
            case 'addon.metadata':
            case 'server.metadata':
                return MetaData::class;
            //API controller
            case 'server.api':
            case 'addon.api':
                return Api::class;
            //Addon controllers
            default:
                return Addon::class;
        }
    }
}
