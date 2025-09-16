<?php

namespace ModulesGarden\TSSGGSModule\Core\Storage;

use ModulesGarden\TSSGGSModule\Core\ModuleConstants;

class Storage
{
    public static function path(...$params): string
    {
        return call_user_func_array([ModuleConstants::class, 'getFullPath'], array_merge(['storage'], $params));
    }
}
