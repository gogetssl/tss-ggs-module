<?php

namespace ModulesGarden\TSSGGSModule\Core\UI\PageParams\ModuleActions;

use ModulesGarden\TSSGGSModule\Core\UI\PageParams\Source\ModuleActionInterface;

class DefaultAction implements ModuleActionInterface
{

    public function selectAppropriateParameters(array $params): array
    {
        return [];
    }
}