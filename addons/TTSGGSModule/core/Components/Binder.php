<?php

namespace ModulesGarden\TTSGGSModule\Core\Components;

use ModulesGarden\TTSGGSModule\Core\Support\Facades\Config;

class Binder
{
    public function __construct()
    {

    }

    public function call(object $obj, string $methodName)
    {
        $className = get_class($obj);
        preg_match('/::(.*)/', $methodName, $matches);
        $full = '\\' . $className . '@' . $matches[1];

        $closure = Config::get('binder.' . $full, null);

        //We do not if provider parameter is real closure because instanceof is so slow... We hope that you know what you are doing
        if (!$closure)
        {
            return;
        }

        $closure->bindTo($obj, $obj)();
    }
}