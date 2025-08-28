<?php

namespace ModulesGarden\TTSGGSModule\Core\UI;

use ModulesGarden\TTSGGSModule\Core\Support\Facades\Store;
use ModulesGarden\TTSGGSModule\Core\UI\PageParams\ExtraParams;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Messages;
use ModulesGarden\TTSGGSModule\Core\Helper\BuildUrl;
use ModulesGarden\TTSGGSModule\Core\Helper\RandomStringGenerator;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\UI\View\AlertsBuilder;

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
            return \ModulesGarden\TTSGGSModule\Core\Support\Facades\Smarty::view($this->template, [
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
                'moduleVersion'   => \ModulesGarden\TTSGGSModule\Core\Support\Facades\Config::get('configuration.version'),
                'integrationType' => 'integration',
            ], ModuleConstants::getTemplateDir() . '/controllers');
        }
        catch (\Exception $ex)
        {
            Messages::alert($ex->getMessage());

            return \ModulesGarden\TTSGGSModule\Core\Support\Facades\Smarty::view($this->template, [
                'rootElements'    => json_encode([
                    'alerts' => (new \ModulesGarden\TTSGGSModule\Core\UI\View\AlertsBuilder())->create()
                ]),
                'currentUrl'      => \ModulesGarden\TTSGGSModule\Core\Helper\BuildUrl::currentUrl(),
                'componentsUrl'   => BuildUrl::getComponentsURL(),
                'extraParams'     => json_encode(ExtraParams::getForCurrentAction()),
                'assetsURL'       => BuildUrl::getAssetsURL(),
                'customAssetsURL' => BuildUrl::getAssetsURL(true),
                'vueInstanceName' => (new RandomStringGenerator(32))->genRandomString(ModuleConstants::getModuleName()),
                'vueStoreData'    => json_encode(Store::toArray()),
                'moduleName' => ModuleConstants::getModuleName(),
                'moduleVersion'   => \ModulesGarden\TTSGGSModule\Core\Support\Facades\Config::get('configuration.version'),
            ], ModuleConstants::getTemplateDir() . '/controllers');
        }
    }
}
