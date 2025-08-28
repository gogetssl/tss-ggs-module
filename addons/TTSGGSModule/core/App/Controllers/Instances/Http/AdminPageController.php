<?php

namespace ModulesGarden\TTSGGSModule\Core\App\Controllers\Instances\Http;

use ModulesGarden\TTSGGSModule\Core\App\Controllers\Instances\HttpController;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\UI\AbstractPartialView;
use ModulesGarden\TTSGGSModule\Core\UI\View;
use ModulesGarden\TTSGGSModule\Core\UI\Views\AddonModuleAdminArea;

class AdminPageController extends HttpController implements \ModulesGarden\TTSGGSModule\Core\Contracts\Controllers\AdminAreaInterface
{
    public function execute($params = null)
    {
        return \ModulesGarden\TTSGGSModule\Core\Support\Facades\Smarty::view('adminarea', parent::run($params), ModuleConstants::getTemplateDir() . '/controllers');
    }

    protected function preResolveResponse()
    {
        if($this->controllerResult instanceof View)
        {
            $this->controllerResult = new AddonModuleAdminArea($this->controllerResult);
        }
    }
}
