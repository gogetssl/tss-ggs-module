<?php

namespace ModulesGarden\TTSGGSModule\Core\Session;

use ModulesGarden\TTSGGSModule\Core\Support\Arr;

class Session
{
    public function exists($name)
    {
        return Arr::exists($_SESSION, $name);
    }

    public function forget($name)
    {
        return Arr::forget($_SESSION, $name);
    }

    public function get($name, $default = null)
    {
        return Arr::get($_SESSION, $name, $default);
    }

    public function set($name, $value)
    {
        return Arr::set($_SESSION, $name, $value);
    }
}
