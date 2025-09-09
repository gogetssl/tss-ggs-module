<?php

namespace ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\Http;

use ModulesGarden\TSSGGSModule\App\Hooks\InternalHooks\PreClientAreaPageLoad;
use ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\HttpController;
use ModulesGarden\TSSGGSModule\Core\Contracts\Controllers\ClientAreaInterface;
use ModulesGarden\TSSGGSModule\Core\ModuleConstants;
use ModulesGarden\TSSGGSModule\Core\Routing\Url;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Breadcrumbs;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Config;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Translator;
use ModulesGarden\TSSGGSModule\Core\UI\View;
use ModulesGarden\TSSGGSModule\Core\UI\Views\AddonModuleClientArea;

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
                'content' => \ModulesGarden\TSSGGSModule\Core\Support\Facades\Smarty::view('clientarea', $vars, ModuleConstants::getTemplateDir() . '/controllers')
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
