<?php

namespace ModulesGarden\TSSGGSModule\Core\App\Config;

use ModulesGarden\TSSGGSModule\Core\Data\Container;
use ModulesGarden\TSSGGSModule\Core\ModuleConstants;

class Metadata
{
    public function updateConfig(Container $container)
    {
        $this->loadModuleVersion($container);
    }

    protected function loadModuleVersion(Container $container)
    {
        if (!file_exists(ModuleConstants::getModuleRootDir() . DIRECTORY_SEPARATOR . 'moduleVersion.php'))
        {
            return;
        }

        include ModuleConstants::getModuleRootDir() . DIRECTORY_SEPARATOR . 'moduleVersion.php';

        $container->set('configuration.version', $moduleVersion);
        $container->set('configuration.description', str_replace(':WIKI_URL:', ($moduleWikiUrl ?: 'https://www.docs.modulesgarden.com/'), $container->get('configuration.description')));
    }

    protected function loadDebugMode(Container $container)
    {
        if (file_exists(ModuleConstants::getModuleRootDir() . DIRECTORY_SEPARATOR . '.debug'))
        {
            $container->set('configuration.debug', true);
        }
    }
}