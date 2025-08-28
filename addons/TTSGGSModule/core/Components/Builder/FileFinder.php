<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Builder;

use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ComponentInterface;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;

class FileFinder
{
    /**
     * @var ComponentInterface
     */
    protected $component;

    public function __construct(string $component)
    {
        $this->component = $component;
    }

    public function getHtml(): string
    {
        return $this->searchForComponentContent('component.html');
    }

    protected function searchForComponentContent(string $file): string
    {
        foreach ($this->getPaths() as $basePath)
        {
            $filePath = $basePath . DIRECTORY_SEPARATOR . $this->component::COMPONENT . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . $file;
            if (file_exists($filePath))
            {
                return $this->getContent($filePath);
            }
        }

        return '';
    }

    protected function getPaths(): array
    {
        static $paths = [];
        if (!$paths)
        {
            $paths = array_merge(
                $this->getUserPath(),
                $this->getAdminPath(),
                $this->getDefaultPath()
            );
        }

        return $paths;
    }

    protected function getUserPath()
    {
        if (!defined('CLIENTAREA'))
        {
            return [];
        }

        $template = '';
        if (!empty($_SESSION['template']))
        {
            $template = $_SESSION['template'];
        }
        else
        {
            global $CONFIG;
            $template = $CONFIG['Template'];
        }

        return [
            ModuleConstants::getFullPath('overrides', 'templates', 'client', $template, 'components'),
            ModuleConstants::getFullPath('overrides', 'templates', 'client', 'default', 'components'),
        ];
    }

    protected function getAdminPath()
    {
        if (!defined('ADMINAREA'))
        {
            return [];
        }

        return [
            ModuleConstants::getFullPath('overrides', 'templates', 'admin', 'default', 'components'),
        ];
    }

    protected function getDefaultPath()
    {
        return [
            ModuleConstants::getFullPath('components'),
        ];
    }

    /**
     * @param $file
     * @return false|string
     */
    protected function getContent($file): string
    {
        return file_get_contents($file);
    }

    //@todo - add option to get paths based on current template

    public function getJs(): string
    {
        return $this->searchForComponentContent('component.js');
    }
}
