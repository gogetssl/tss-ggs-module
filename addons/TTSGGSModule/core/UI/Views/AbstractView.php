<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Views;

use ModulesGarden\TTSGGSModule\Core\Components\DataBuilder;
use ModulesGarden\TTSGGSModule\Core\Helper\BuildUrl;
use ModulesGarden\TTSGGSModule\Core\Helper\RandomStringGenerator;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Store;
use ModulesGarden\TTSGGSModule\Core\UI\AbstractPartialView;
use ModulesGarden\TTSGGSModule\Core\UI\PageParams\ExtraParams;
use ModulesGarden\TTSGGSModule\Core\UI\View\AlertsBuilder;
use ModulesGarden\TTSGGSModule\Core\UI\View\BreadcrumbsBuilder;
use ModulesGarden\TTSGGSModule\Core\UI\View\FooterBuilder;
use ModulesGarden\TTSGGSModule\Core\UI\View\NavBarBuilder;
use function ModulesGarden\TTSGGSModule\Core\Helper\isAdmin;

class AbstractView
{
    protected AbstractPartialView $view;

    public function __construct(AbstractPartialView $view)
    {
        $this->view = $view;
    }

    public function getResponse(): array
    {
        return array_merge([
            'rootElements' => json_encode(array_merge(
                $this->getBody(),
                $this->getNavbar(),
                $this->getBreadCrumb(),
                $this->getAlerts(),
                $this->getFooter(),
            )),
        ], $this->getBaseElements());
    }

    protected function getBaseElements(): array
    {
        return [
            'currentUrl'      => BuildUrl::currentUrl(),
            'componentsUrl'   => BuildUrl::getComponentsURL(),
            'extraParams'     => json_encode(ExtraParams::getForCurrentAction()),
            'assetsURL'       => BuildUrl::getAssetsURL(),
            'customAssetsURL' => BuildUrl::getAssetsURL(true),
            'vueInstanceName' => (new RandomStringGenerator(32))->genRandomString(ModuleConstants::getModuleName()),
            'vueStoreData'    => json_encode(Store::toArray()),
            'moduleName'      => ModuleConstants::getModuleName(),
            'moduleVersion'   => \ModulesGarden\TTSGGSModule\Core\Support\Facades\Config::get('configuration.version'),
            'integrationType' => 'module',
            'isAdminArea'     => isAdmin() ? 1 : 0,
        ];
    }

    protected function getBody(): array
    {
        return [
            'body' => $this->buildRootElements($this->view->getElements())
        ];
    }

    protected function getNavbar(): array
    {
        return [
            'navbar' => isAdmin() ? $this->buildRootElements([(new NavBarBuilder())->createAdminArea()])[0] : $this->buildRootElements([(new NavBarBuilder())->createClientArea()])[0],
        ];
    }

    protected function getBreadCrumb(): array
    {
        return [
            'breadcrumb' => $this->buildRootElements([(new BreadcrumbsBuilder())->create()])[0]
        ];
    }

    protected function getFooter(): array
    {
        return [
            'footer' => $this->buildRootElements([(new FooterBuilder())->create()])[0]
        ];
    }

    protected function getAlerts(): array
    {
        return [
            'alerts' => (new AlertsBuilder())->create()
        ];
    }

    //@todo refactor me
    protected function buildRootElements(array $rootElements)
    {
        return array_map(function($element) {
            return (new DataBuilder($element))
                ->withHtml()
                ->toArray();
        }, $rootElements);
    }
}