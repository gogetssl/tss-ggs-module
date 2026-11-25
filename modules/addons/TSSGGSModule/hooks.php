<?php

// only through WHMCS: a direct request gets nothing (no output, no error with the server path)
if(!defined('WHMCS'))
{
    http_response_code(403);
    exit;
}

// an update of the module that stopped half way (its process died) is undone here (App\Libs\Update\Swap)
$moduleUpdateJournal = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.' . strtolower(basename(__DIR__)) . '-update' . DIRECTORY_SEPARATOR . 'journal.json';
if(is_file($moduleUpdateJournal) && is_file(dirname($moduleUpdateJournal) . DIRECTORY_SEPARATOR . 'recover.php'))
{
    include dirname($moduleUpdateJournal) . DIRECTORY_SEPARATOR . 'recover.php';
}


require_once __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Kernel' . DIRECTORY_SEPARATOR . 'Boot.php';

// the addon's own hooks: one file per hook point in app/Hooks
\TSSGGSModule\App\Kernel\Hooks::load(__DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Hooks');

// Client area hooks of the server module (sidebar actions, SSL summary, SAN upgrades)
$serverHooks = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'servers' . DIRECTORY_SEPARATOR . 'TSSGGSModule' . DIRECTORY_SEPARATOR . 'hooks.php';
if(file_exists($serverHooks))
{
    require_once $serverHooks;
}

// while a migration moves the legacy module's services, that module is paused for them (requests, commands, crons)
\TSSGGSModule\App\Migration\LegacyBlock::register();
