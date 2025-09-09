<?php

namespace ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\Http;

use ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\HttpController;
use ModulesGarden\TSSGGSModule\Core\Contracts\Controllers\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Controllers\ClientAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Http\JsonResponse;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Params;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TSSGGSModule\Core\UI\View;

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
            $error = new \ModulesGarden\TSSGGSModule\Components\BlockError\BlockError();
            $error->setException(Params::get('exception'));

            $view = new View();
            $view->addElement($error);

            return $view;
        }
    }
}
