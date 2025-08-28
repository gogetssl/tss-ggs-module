<?php

namespace ModulesGarden\TTSGGSModule\Core\DependencyInjection;

use ModulesGarden\TTSGGSModule\Core\ModuleConstants;

/**
 * Load all services from yml file and mark them as shared in DI container
 */
class Services
{
    /**
     * Services constructor.
     */
    public function __construct()
    {
        $this->loadServices();
        $this->loadAliases();
    }

    /**
     * Load all needed servies to DI container
     */
    protected function loadServices()
    {
        foreach ($this->getFilesList() as $file)
        {
            if (!file_exists($file))
            {
                continue;
            }

            $servicesList = include $file;
            if (!is_array($servicesList) || empty($servicesList))
            {
                continue;
            }

            $this->registerServices($servicesList);
        }
    }

    protected function loadAliases()
    {
        $aliases = include ModuleConstants::getFullPath('core', 'Config', 'di', 'aliases.php');
        foreach ($aliases as $abstract => $alias)
        {
            Container::getInstance()->bind($abstract, $alias);
        }
    }

    /**
     * Get file list with servies configuration
     * @return array
     */
    protected function getFilesList()
    {
        return [
            ModuleConstants::getFullPath('app', 'Config', 'di', 'services.php'),
            ModuleConstants::getFullPath('core', 'Config', 'di', 'services.php'),
        ];
    }

    /**
     * Register all services in DI container
     * @param $servicesList
     */
    protected function registerServices($servicesList)
    {
        foreach ($servicesList as $service)
        {
            Container::getInstance()->singleton($service);
        }
    }
}
