<?php

namespace ModulesGarden\TTSGGSModule\Core\App\Controllers\Instances\Http;

use ModulesGarden\TTSGGSModule\Core\App\Controllers\Instances\HttpController;
use ModulesGarden\TTSGGSModule\Core\Contracts\Controllers\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Controllers\ClientAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Http\JsonResponse;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Params;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TTSGGSModule\Core\UI\View;

//@todo refactor
class ErrorPage extends HttpController implements AdminAreaInterface, ClientAreaInterface
{
    public function execute($params = null)
    {
        Params::createFrom((array)$params);

        return $this->resolveResponse();
    }

    public function resolveResponse()
    {
        if (Request::get('ajax'))
        {
            return (new JsonResponse())->withError(Params::get('exception')->getMessage());
        }
        else
        {
            $error = new \ModulesGarden\TTSGGSModule\Components\BlockError\BlockError();
            $error->setException(Params::get('exception'));

            $view = new View();
            $view->addElement($error);

            return $view;
        }
    }
}
