<?php

namespace ModulesGarden\TSSGGSModule\Core\CommandLine\LoadCommands;

use ModulesGarden\TSSGGSModule\Core\DependencyInjection\PackageServices;
use function ModulesGarden\TSSGGSModule\Core\make;

class LoadCommandsControllersPackageServices implements LoadCommandsControllersInterface
{
    public function getCommands(string $dir = null): array
    {
        $commands = make(PackageServices::class)->getCommands();

        return $commands;
    }
}
