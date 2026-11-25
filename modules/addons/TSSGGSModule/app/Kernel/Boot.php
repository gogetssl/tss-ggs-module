<?php

/**
 * Loads the module's classes: TSSGGSModule\App\Libs\Logs\Logger is app/Libs/Logs/Logger.php.
 * Required once by every entry point (the addon file, hooks.php, cron/cron.php, the server module comes through
 * hooks.php). Nothing else happens here: no services are started, no database is read.
 */

if(!defined('WHMCS'))
{
    http_response_code(403);
    exit;
}

if(!defined('TSSGGSMODULE_BOOTED'))
{
    define('TSSGGSMODULE_BOOTED', true);

    spl_autoload_register(function($class) {
        $prefix = 'TSSGGSModule\\App\\';
        if(strncmp($class, $prefix, strlen($prefix)) !== 0)
        {
            return;
        }
        $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix))) . '.php';
        if(is_file($file))
        {
            require_once $file;
        }
    });
}
