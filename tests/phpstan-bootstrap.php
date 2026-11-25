<?php

// PHPStan reads the module without WHMCS: constants WHMCS defines at runtime, and the module's autoloader
if(!defined('WHMCS'))
{
    define('WHMCS', true);
}
if(!defined('ROOTDIR'))
{
    define('ROOTDIR', dirname(__DIR__));
}

spl_autoload_register(function($class) {
    $prefix = 'ModulesGarden\\TSSGGSModule\\App\\';
    if(strncmp($class, $prefix, strlen($prefix)) === 0)
    {
        $file = __DIR__ . '/../modules/addons/TSSGGSModule/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if(is_file($file))
        {
            require_once $file;
        }
    }
});
