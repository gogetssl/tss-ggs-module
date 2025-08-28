<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\PageParams\Source;

interface ModuleActionInterface
{
    public function selectAppropriateParameters(array $params): array;
}