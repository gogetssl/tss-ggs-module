<?php

namespace ModulesGarden\TTSGGSModule\Core\App\Config;

use ModulesGarden\TTSGGSModule\Core\Data\Container;
use ModulesGarden\TTSGGSModule\Core\FileReader\Reader;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\Support\Arr;

class Config
{
    protected Container $container;
    protected Container $configs;
    protected array $default = [
        'di',
        'configuration',
        'packages'
    ];
    protected array $extensions = [
        'yml',
        'php',
        'json'
    ];

    public function __construct()
    {
        $this->createContainers();
        $this->searchForConfigFiles();
        $this->loadDefaults();
        $this->loadMetadata();
    }


    public function get(string $name, $default = null)
    {
        $root = explode('.', $name, 2)[0] ?? null;

        if (!$this->container->offsetExists($root))
        {

            $this->loadConfig($root);
        }

        return $this->container->get($name, $default);
    }

    public function push(string $name, $value)
    {
        $this->container->push($name, $value);
    }

    protected function createContainers()
    {
        $this->container = new Container();
        $this->configs   = new Container();
    }

    protected function loadDefaults()
    {
        foreach ($this->default as $default)
        {
            $this->loadConfig($default);
        }
    }

    private function searchForConfigFiles()
    {
        foreach ($this->getDirs() as $dir)
        {
            foreach ($this->extensions as $extension)
            {
                $files = array_unique(glob($dir . '/{,*/,*/*}*.' . $extension, GLOB_BRACE));
                foreach ($files as $file)
                {
                    $info   = pathinfo($file);
                    $subdir = trim(str_replace($dir, '', $info['dirname']), '/');

                    $this->configs->push($subdir ? $subdir . '.' . $info['filename'] : $info['filename'], $file);
                }
            }
        }
    }

    protected function loadConfig(string $name)
    {
        $files = Arr::dot((array)$this->configs->get($name, []));

        foreach ($files as $file)
        {
            $this->loadFile($file);
        }
    }

    private function loadFile(string $file): void
    {
        $content = Reader::read($file);
        $info    = pathinfo($file);
        $subdir  = explode("/Config/", dirname($file), 2)[1] ?? null;

        $this->container->set($subdir ? $subdir . '.' . $info['filename'] : $info['filename'], $content->toArray());
    }

    private function getDirs(): array
    {
        return [
            ModuleConstants::getFullPath('core', 'Config'),
            ModuleConstants::getFullPath('app', 'Config'),
            ModuleConstants::getFullPath('overrides', 'config'),
        ];
    }

    private function loadMetadata()
    {
        (new Metadata())->updateConfig($this->container);
    }
}