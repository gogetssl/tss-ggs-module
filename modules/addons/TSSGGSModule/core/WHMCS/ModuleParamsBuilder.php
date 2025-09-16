<?php

namespace ModulesGarden\TSSGGSModule\Core\WHMCS;

use ModulesGarden\TSSGGSModule\Core\ModuleConstants;

class ModuleParamsBuilder
{
    public static function get($relId)
    {
        if (!function_exists('ModuleBuildParams'))
        {
            require_once ModuleConstants::getFullPathWhmcs('includes') . DIRECTORY_SEPARATOR . "modulefunctions.php";
        }

        return \ModuleBuildParams($relId);
    }
}