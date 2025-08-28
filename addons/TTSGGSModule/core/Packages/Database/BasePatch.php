<?php

namespace ModulesGarden\TTSGGSModule\Core\Packages\Database;

abstract class BasePatch implements PatchInterface
{
    public function requires(): array
    {
        return [];
    }
}