<?php

namespace ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\Http;

use ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\HttpController;
use ModulesGarden\TSSGGSModule\Core\ModuleConstants;
use ModulesGarden\TSSGGSModule\Core\UI\AbstractPartialView;
use ModulesGarden\TSSGGSModule\Core\UI\View;
use ModulesGarden\TSSGGSModule\Core\UI\Views\AddonModuleAdminArea;

class AdminPageController extends HttpController implements \ModulesGarden\TSSGGSModule\Core\Contracts\Controllers\AdminAreaInterface
{
    public function execute($params = null)
    {
        return \ModulesGarden\TSSGGSModule\Core\Support\Facades\Smarty::view('adminarea', parent::run($params), ModuleConstants::getTemplateDir() . '/controllers');
    }

    protected function preResolveResponse()
    {
        if($this->controllerResult instanceof View)
        {
            $this->controllerResult = new AddonModuleAdminArea($this->controllerResult);
        }
    }
}
