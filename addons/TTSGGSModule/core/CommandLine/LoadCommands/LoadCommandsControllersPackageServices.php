<?php

namespace ModulesGarden\TTSGGSModule\Core\CommandLine\LoadCommands;

use ModulesGarden\TTSGGSModule\Core\DependencyInjection\PackageServices;
use function ModulesGarden\TTSGGSModule\Core\make;

class LoadCommandsControllersPackageServices implements LoadCommandsControllersInterface
{
    public function getCommands(string $dir = null): array
    {
        $commands = make(PackageServices::class)->getCommands();

        return $commands;
    }
}
