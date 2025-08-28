<?php

namespace ModulesGarden\TTSGGSModule\Core\Support;

class Text
{
    public static function toNamespace($class)
    {
        $name = is_string($class) ? $class : get_class($class);

        return str_replace('\\', '_', $name);
    }

    public static function toUnderscore($input)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    public static function toKebab($input)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $input));
    }
}
