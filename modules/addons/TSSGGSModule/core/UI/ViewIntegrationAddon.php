<?php

namespace ModulesGarden\TSSGGSModule\Core\UI;

use ModulesGarden\TSSGGSModule\Core\Support\Facades\Store;
use ModulesGarden\TSSGGSModule\Core\UI\PageParams\ExtraParams;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Messages;
use ModulesGarden\TSSGGSModule\Core\Helper\BuildUrl;
use ModulesGarden\TSSGGSModule\Core\Helper\RandomStringGenerator;
use ModulesGarden\TSSGGSModule\Core\ModuleConstants;
use ModulesGarden\TSSGGSModule\Core\UI\View\AlertsBuilder;

/**
 * Integration Addon View Controller
 */
class ViewIntegrationAddon extends View
{
    protected $integration = true;
    protected $template = 'integration';

    //@todo move it to views and controller
    public function getResponse()
    {
        try {
            return \ModulesGarden\TSSGGSModule\Core\Support\Facades\Smarty::view($this->template, [
                'rootElements'    => json_encode([
                    'body'       => $this->buildRootElements($this->elements),
                    'alerts'     => (new AlertsBuilder())->create()
                ]),
                'currentUrl'      => BuildUrl::currentUrl(),
                'componentsUrl'   => BuildUrl::getComponentsURL(),
                'extraParams'     => json_encode(ExtraParams::getForCurrentAction()),
                'assetsURL'       => BuildUrl::getAssetsURL(),
                'customAssetsURL' => BuildUrl::getAssetsURL(true),
                'vueInstanceName' => (new RandomStringGenerator(32))->genRandomString(ModuleConstants::getModuleName()),
                'vueStoreData'    => json_encode(Store::toArray()),
                'moduleName'      => ModuleConstants::getModuleName(),
                'moduleVersion'   => \ModulesGarden\TSSGGSModule\Core\Support\Facades\Config::get('configuration.version'),
                'integrationType' => 'integration',
            ], ModuleConstants::getTemplateDir() . '/controllers');
        }
        catch (\Exception $ex)
        {
            Messages::alert($ex->getMessage());

            return \ModulesGarden\TSSGGSModule\Core\Support\Facades\Smarty::view($this->template, [
                'rootElements'    => json_encode([
                    'alerts' => (new \ModulesGarden\TSSGGSModule\Core\UI\View\AlertsBuilder())->create()
                ]),
                'currentUrl'      => \ModulesGarden\TSSGGSModule\Core\Helper\BuildUrl::currentUrl(),
                'componentsUrl'   => BuildUrl::getComponentsURL(),
                'extraParams'     => json_encode(ExtraParams::getForCurrentAction()),
                'assetsURL'       => BuildUrl::getAssetsURL(),
                'customAssetsURL' => BuildUrl::getAssetsURL(true),
                'vueInstanceName' => (new RandomStringGenerator(32))->genRandomString(ModuleConstants::getModuleName()),
                'vueStoreData'    => json_encode(Store::toArray()),
                'moduleName' => ModuleConstants::getModuleName(),
                'moduleVersion'   => \ModulesGarden\TSSGGSModule\Core\Support\Facades\Config::get('configuration.version'),
            ], ModuleConstants::getTemplateDir() . '/controllers');
        }
    }
}
