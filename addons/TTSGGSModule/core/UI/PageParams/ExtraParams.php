<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\PageParams;

use ModulesGarden\TTSGGSModule\Core\Support\Facades\Params;

class ExtraParams
{
    public static function getForCurrentAction():array
    {
        $params = Params::all();

        $moduleAction = ModuleActionsFactory::getFromParams($params);

        return $moduleAction->selectAppropriateParameters($params);
    }
}
