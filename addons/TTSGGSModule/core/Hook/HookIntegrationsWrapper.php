<?php

namespace ModulesGarden\TTSGGSModule\Core\Hook;

use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Smarty;
use ModulesGarden\TTSGGSModule\Core\Traits\IsAdmin;
use ModulesGarden\TTSGGSModule\Core\Traits\Lang;

class HookIntegrationsWrapper
{
    use IsAdmin;
    use Lang;

    protected $integrations = [];
    protected $templateDirectory = null;
    protected $templateName = 'integrationsWrapper';

    public function __construct($integrations = [])
    {
        if (is_array($integrations))
        {
            $this->integrations = $integrations;
        }

        $this->templateDirectory = ModuleConstants::getResourcesDir() . DIRECTORY_SEPARATOR . 'views'
                                   . DIRECTORY_SEPARATOR . 'controllers';
    }

    public function getHtml()
    {
        foreach ($this->integrations as $key => $integration)
        {
            if (!$integration['htmlData'] || $integration['htmlData'] === '' || !is_string($integration['htmlData'])
                || !$integration['integrationDetails'] || !is_object($integration['integrationDetails']))
            {
                unset($this->integrations[$key]);
            }
        }

        if (!$this->integrations)
        {
            return null;
        }


        $integrationHtml = Smarty::view($this->templateName, $this->getWrapperData(), $this->templateDirectory);

        return $integrationHtml;
    }

    protected function getWrapperData()
    {
        return [
            'integrations' => $this->integrations,
        ];
    }
}
