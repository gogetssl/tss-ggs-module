<?php

namespace ModulesGarden\TSSGGSModule\Core\UI\PageParams\Source;

interface ModuleActionInterface
{
    public function selectAppropriateParameters(array $params): array;
}