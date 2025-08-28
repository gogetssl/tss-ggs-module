<?php

namespace ModulesGarden\TTSGGSModule\Core\App\Controllers;

use ModulesGarden\TTSGGSModule\Core\Support\Facades\Params;

abstract class AppController
{
    protected array $params;

    public function __construct(array $params = [])
    {
        Params::createFrom($params);
    }

    public function runController($callerName, $params)
    {
        $controller = $this->getControllerInstanceClass($callerName, $params);

        return (new $controller)->runExecuteProcess($params);
    }
}
