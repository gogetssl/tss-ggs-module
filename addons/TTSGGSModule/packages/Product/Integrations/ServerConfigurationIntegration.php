<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\Integrations;

use ModulesGarden\TTSGGSModule\Core\Hook\AbstractHookIntegrationController;
use ModulesGarden\TTSGGSModule\Packages\Product\Http\Admin\ServerConfig;

class ServerConfigurationIntegration extends AbstractHookIntegrationController
{
    public function __construct()
    {
        $this->addIntegration('configservers',
            ['action' => 'manage'],
            [ServerConfig::class, 'index'],
            '#serverHash',
            self::TYPE_AFTER,
            self::INSERT_TYPE_FULL);
    }
}