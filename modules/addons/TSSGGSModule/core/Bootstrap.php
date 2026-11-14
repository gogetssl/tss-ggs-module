<?php

// only through WHMCS: a direct request gets nothing (no output, no error with the server path)
if(!defined('WHMCS'))
{
    http_response_code(403);
    exit;
}


//boostrap
use ModulesGarden\TSSGGSModule\Core\DependencyInjection\PackageServices;
use ModulesGarden\TSSGGSModule\Core\DependencyInjection\Services;
use ModulesGarden\TSSGGSModule\Core\ModuleConstants;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Helper' . DIRECTORY_SEPARATOR . 'Functions.php';

/**
 * Initialize base values
 */
ModuleConstants::initialize();

/**
 * Initialize Services
 */
new Services();

/**
 * Boot packages
 */
\ModulesGarden\TSSGGSModule\Core\make(PackageServices::class);