<?php

/**
 * Unit tests run against the module source (namespace TSSGGSModule) without WHMCS: the autoloader
 * below resolves the module's App\ classes. Code that needs WHMCS or its database belongs
 * in tests/Smoke (run inside a WHMCS install by tools/qa.sh), not here.
 */

define('TSSGGS_TESTS', true);

// the module's classes: App\ is modules/addons/TSSGGSModule/app (what app/Kernel/Boot.php does inside WHMCS)
spl_autoload_register(function($class) {
    $prefix = 'TSSGGSModule\\App\\';
    if(strncmp($class, $prefix, strlen($prefix)) === 0)
    {
        $file = __DIR__ . '/../modules/addons/TSSGGSModule/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if(is_file($file))
        {
            require_once $file;
        }
    }
    // the server module: TSSGGSModule\Server\X is modules/servers/TSSGGSModule/X.php (its Boot.php inside WHMCS)
    $prefix = 'TSSGGSModule\\Server\\';
    if(strncmp($class, $prefix, strlen($prefix)) === 0)
    {
        $file = __DIR__ . '/../modules/servers/TSSGGSModule/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if(is_file($file))
        {
            require_once $file;
        }
    }
});
