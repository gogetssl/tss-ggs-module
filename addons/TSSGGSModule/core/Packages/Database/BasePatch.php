<?php

namespace ModulesGarden\TSSGGSModule\Core\Packages\Database;

abstract class BasePatch implements PatchInterface
{
    public function requires(): array
    {
        return [];
    }
}