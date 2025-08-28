<?php

namespace ModulesGarden\TTSGGSModule\Core\DependencyInjection;

use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\Packages\Config;
use ModulesGarden\TTSGGSModule\Core\Packages\Packages;

class PackageServices
{
    protected array $commands = [];
    protected array $httpControllers = [];
    protected array $menu = [];
    protected array $configs = [];

    public function __construct()
    {
        $this->load();
    }

    protected function load(): void
    {
        $packages = $this->getEnabledPackages();
        $this->registerPackages($packages);
    }

    protected function registerPackages(array $packages)
    {
        $loaded = [];
        while ($packages)
        {
            foreach ($packages as $package => $config)
            {
                $requiredPackages = $config->get('packages', []);

                if (is_callable($requiredPackages))
                {
                    $requiredPackages = $requiredPackages();
                }

                if ($notInstalled = array_diff($requiredPackages, array_merge(array_keys($packages), $loaded)))
                {
                    //@todo change to exception
                    die(sprintf('[%s]Package %s requires %s', ModuleConstants::getModuleName(), $package, implode(',', $notInstalled)));
                }

                if ($requiredPackages && array_diff($requiredPackages, $loaded))
                {
                    continue;
                }

                $this->registerServices($config, $package);
                unset($packages[$package]);

                $loaded[] = $package;
            }
        }
    }

    protected function getEnabledPackages(): array
    {
        $packages = [];
        foreach ($this->getFilesList() as $package => $file)
        {
            if (\ModulesGarden\TTSGGSModule\Core\Support\Facades\Config::get('packages.' . $package, false))
            {
                $packages[$package] = new Config($file);
            }
        }


        return $packages;
    }

    protected function getFilesList(): array
    {
        return (new Packages())->getPackagesInstallFiles();
    }

    protected function registerServices(Config $config, $package)
    {
        $this->registerCommands($config);
        $this->registerMenu($config);
        $this->registerControllers($config);
        $this->callPackageBootstrap($config);
        $this->registerConfig($config, $package);
    }

    protected function registerCommands(Config $config)
    {
        foreach ($config->get('commands', []) as $service)
        {
            $this->commands[] = $service;
        }
    }

    protected function registerMenu(Config $config)
    {
        foreach ($config->get('menu', []) as $key => $service)
        {
            foreach ($service as $keyItem => $item)
            {
                $this->menu[$key][$keyItem] = $item;
            }
        }
    }

    protected function registerControllers(Config $config)
    {
        foreach ($config->get('controllers', []) as $key => $service)
        {
            foreach ($service as $keyItem => $item)
            {
                $this->httpControllers[$key][] = $item;
            }
        }
    }

    protected function callPackageBootstrap(Config $config)
    {
        if (is_callable($config->get('bootstrap')))
        {
            $config->get('bootstrap')();
        }
    }

    protected function registerConfig(Config $config, $package)
    {
        if ($config->get('config'))
        {
            \ModulesGarden\TTSGGSModule\Core\Support\Facades\Config::push('install.' . lcfirst($package), $config->get('config'));
        }
    }

    public function getCommands(): array
    {
        return $this->commands;
    }

    public function getHttpControllers(): array
    {
        return $this->httpControllers;
    }

    public function getMenu(): array
    {
        return $this->menu;
    }
}
