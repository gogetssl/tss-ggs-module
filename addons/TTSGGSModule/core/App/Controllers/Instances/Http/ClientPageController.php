<?php

namespace ModulesGarden\TTSGGSModule\Core\App\Controllers\Instances\Http;

use ModulesGarden\TTSGGSModule\App\Hooks\InternalHooks\PreClientAreaPageLoad;
use ModulesGarden\TTSGGSModule\Core\App\Controllers\Instances\HttpController;
use ModulesGarden\TTSGGSModule\Core\Contracts\Controllers\ClientAreaInterface;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\Routing\Url;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Breadcrumbs;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Config;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Translator;
use ModulesGarden\TTSGGSModule\Core\UI\View;
use ModulesGarden\TTSGGSModule\Core\UI\Views\AddonModuleClientArea;

class ClientPageController extends HttpController implements ClientAreaInterface
{
    public function execute($params = null)
    {
        $vars = parent::run($params);

        return [
            'pagetitle'    => Translator::get(Config::get('configuration.clientAreaName', ModuleConstants::getModuleName())),
            'breadcrumb'   => $this->getBreadcrumbs(),
            'templatefile' => 'resources/whmcs/clientarea',
            'requirelogin' => true,
            'forcessl'     => false,
            'vars'         => [
                'content' => \ModulesGarden\TTSGGSModule\Core\Support\Facades\Smarty::view('clientarea', $vars, ModuleConstants::getTemplateDir() . '/controllers')
            ]
        ];
    }

    protected function preResolveResponse()
    {
        if ($this->controllerResult instanceof View)
        {
            $this->controllerResult = new AddonModuleClientArea($this->controllerResult);
        }
    }

    protected function getBreadcrumbs(): array
    {
        $breadcrumbs = [
            Url::route() => Translator::get(Config::get('configuration.clientAreaName'))
        ];

        foreach (Breadcrumbs::get() as $breadcrumb)
        {
            $breadcrumbs[$breadcrumb->getUrl()] = Translator::get($breadcrumb->getName());
        }

        return $breadcrumbs;
    }
}
