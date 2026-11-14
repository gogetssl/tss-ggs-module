<?php

// only through WHMCS: a direct request gets nothing (no output, no error with the server path)
if(!defined('WHMCS'))
{
    http_response_code(403);
    exit;
}


require_once __DIR__ . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Bootstrap.php';

\ModulesGarden\TSSGGSModule\Core\Hook\HookManager::create(__DIR__);

// Client area hooks of the server module (sidebar actions, SSL summary, SAN upgrades)
$serverHooks = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'servers' . DIRECTORY_SEPARATOR . 'TSSGGSModule' . DIRECTORY_SEPARATOR . 'hooks.php';
if(file_exists($serverHooks))
{
    require_once $serverHooks;
}

// while a migration moves the legacy module's services, that module is paused for them (requests, commands, crons)
\ModulesGarden\TSSGGSModule\App\Migration\LegacyBlock::register();
