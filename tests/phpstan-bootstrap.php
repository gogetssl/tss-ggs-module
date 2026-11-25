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

require_once __DIR__ . '/../modules/addons/TSSGGSModule/vendor/autoload.php';
