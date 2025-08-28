<?php

namespace ModulesGarden\TTSGGSModule\Core\Hook;

use ModulesGarden\TTSGGSModule\Core\Hook\Integration\Enums\IntegrationInsertTypes;
use ModulesGarden\TTSGGSModule\Core\Hook\Integration\Enums\IntegrationTypes;
use ModulesGarden\TTSGGSModule\Core\Hook\Integration\Integration;
use ModulesGarden\TTSGGSModule\Core\Hook\Integration\Models\ControllerCallback;


abstract class AbstractHookIntegrationController
{
    /**
     * @deprecated - use IntegrationInsertTypes Enum instead
     */
    public const INSERT_TYPE_CONTENT = 'content';
    /**
     * @deprecated - use IntegrationInsertTypes Enum instead
     */
    public const INSERT_TYPE_FULL = 'full';
    /**
     * @deprecated - use IntegrationInsertTypes Enum instead
     */
    public const INSERT_TYPE_MC_CONTENT = 'mc_content';

    /**
     * @deprecated - use IntegrationTypes Enum instead
     */
    public const TYPE_AFTER = 'after';
    /**
     * @deprecated - use IntegrationTypes Enum instead
     */
    public const TYPE_APPEND = 'append';
    /**
     * @deprecated - use IntegrationTypes Enum instead
     */
    public const TYPE_BEFORE = 'before';
    /**
     * @deprecated - use IntegrationTypes Enum instead
     */
    public const TYPE_CUSTOM = 'custom';
    /**
     * @deprecated - use IntegrationTypes Enum instead
     */
    public const TYPE_PREPEND = 'prepend';
    /**
     * @deprecated - use IntegrationTypes Enum instead
     */
    public const TYPE_REPLACE = 'replace';

    protected Integration $integration;

    /**
     * @deprecated - use setIntegration instead
     */
    public function addIntegration(string $fileName = null, array $requestParams = [], $controllerCallback = null, $jqSelector = null,
                                   $integrationType = null, $jsFunctionName = null, $insertIntegrationType = self::INSERT_TYPE_FULL):void
    {
        if (is_array($controllerCallback))
        {
            $controllerCallback = new ControllerCallback($controllerCallback[0], $controllerCallback[1]);
        }

        if (!is_a($controllerCallback, ControllerCallback::class))
        {
            throw new \InvalidArgumentException("ControllerCallback must be an array or a class implementing " . ControllerCallback::class);
        }

        $integration = new Integration($controllerCallback, $jqSelector);

        if (!empty($fileName))
        {
            $integration->requireFile($fileName);
        }

        if (!empty($requestParams))
        {
            $integration->requireRequestFields($requestParams);
        }

        if (!empty($jsFunctionName))
        {
            $integration->setJsFunctionName($jsFunctionName);
        }

        if ($type = IntegrationTypes::tryFrom($integrationType))
        {
            $integration->setType($type);
        }

        if ($insertType = IntegrationInsertTypes::tryFrom($insertIntegrationType))
        {
            $integration->setInsertType($insertType);
        }

        $this->setIntegration($integration);
    }

    public function setIntegration(Integration $integration):self
    {
        $this->integration = $integration;

        return $this;
    }

    public function getIntegration():Integration
    {
        if (!isset($this->integration))
        {
            throw new \RuntimeException("Integration not set");
        }

        return $this->integration;
    }
}
