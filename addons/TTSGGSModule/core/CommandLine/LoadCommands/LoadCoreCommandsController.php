<?php

namespace ModulesGarden\TTSGGSModule\Core\CommandLine\LoadCommands;

use ModulesGarden\TTSGGSModule\Core\ModuleConstants;

class LoadCoreCommandsController implements LoadCommandsControllersInterface
{
    public function getCommands(string $dir = null): array
    {
        $files    = $this->getFiles($dir);
        $commands = [];

        $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
        foreach ($files as $file)
        {
            $commands[] = ModuleConstants::getRootNamespace() . '\Core\\' . $dir . '\\' . $file;
        }

        return $commands;
    }

    protected function getFiles(string $dir): array
    {
        $files    = glob(ModuleConstants::getFullPath('core', $dir) . DIRECTORY_SEPARATOR . '*.php');
        $commands = [];

        foreach ($files as $file)
        {
            $file = substr($file, strrpos($file, DIRECTORY_SEPARATOR) + 1);
            $file = substr($file, 0, strrpos($file, '.'));

            $commands[] = $file;
        }

        return $commands;
    }
}