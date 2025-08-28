<?php

namespace ModulesGarden\TTSGGSModule\Core\CommandLine\LoadCommands;

interface LoadCommandsControllersInterface
{
    public function getCommands(string $dir = null): array;
}
