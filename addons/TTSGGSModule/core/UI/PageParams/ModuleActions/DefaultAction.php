<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\PageParams\ModuleActions;

use ModulesGarden\TTSGGSModule\Core\UI\PageParams\Source\ModuleActionInterface;

class DefaultAction implements ModuleActionInterface
{

    public function selectAppropriateParameters(array $params): array
    {
        return [];
    }
}