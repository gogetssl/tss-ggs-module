<?php

namespace ModulesGarden\TTSGGSModule\Core\CommandLine;

class CommandHelper
{
    public static function calledViaCli():bool
    {
        return in_array(php_sapi_name(), ["cli", "cli-server"]) ||
               (!isset($_SERVER["SERVER_NAME"]) && !isset($_SERVER["HTTP_HOST"]));
    }
}