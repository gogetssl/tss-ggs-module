<?php

namespace ModulesGarden\TSSGGSModule\Core\CommandLine\LoadCommands;

interface LoadCommandsControllersInterface
{
    public function getCommands(string $dir = null): array;
}
