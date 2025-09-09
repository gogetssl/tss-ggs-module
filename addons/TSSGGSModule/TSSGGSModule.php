<?php

if (!defined('WHMCS'))
{
    die('This file cannot be accessed directly');
}

#MGLICENSE_FUNCTIONS#

//Include proper base file
$moduleType = substr(ucfirst(basename(dirname(__FILE__, 2))), 0, -1);
require __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Configuration' . DIRECTORY_SEPARATOR . $moduleType . DIRECTORY_SEPARATOR . 'base.php';
