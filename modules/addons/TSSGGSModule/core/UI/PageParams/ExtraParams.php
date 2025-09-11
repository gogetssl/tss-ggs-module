<?php

namespace ModulesGarden\TSSGGSModule\Core\UI\PageParams;

use ModulesGarden\TSSGGSModule\Core\Support\Facades\Params;

class ExtraParams
{
    public static function getForCurrentAction():array
    {
        $params = Params::all();

        $moduleAction = ModuleActionsFactory::getFromParams($params);

        return $moduleAction->selectAppropriateParameters($params);
    }
}
