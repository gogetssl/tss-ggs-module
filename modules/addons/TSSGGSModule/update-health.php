<?php

/**
 * Health check of the module updater (App\Libs\Update\Health). Called only by the updater itself, with a one-time
 * token whose hash it left in modules/.<folder>-update/health.json; without a valid token it answers nothing.
 * Loading WHMCS here loads every active addon's hooks.php - the new version of this module included - so code that
 * fails on this server fails here, before an update is kept.
 *
 *   mode "reset"    clears the web server's OPcache (checked before WHMCS is loaded, so stale code never runs here)
 *   mode "ping"     the running version answers
 *   mode "upgrade"  after the swap: runs WHMCS's addon upgrade from the previous version, then answers
 */

// from the command line (App\Libs\Update\Health::callCli) the token comes in the environment
$token = PHP_SAPI === 'cli' ? (string)getenv('TSSGGS_HEALTH_TOKEN') : (isset($_GET['token']) ? (string)$_GET['token'] : '');
$file  = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.' . strtolower(basename(__DIR__)) . '-update' . DIRECTORY_SEPARATOR . 'health.json';
$stored = preg_match('/^[0-9a-f]{64}$/', $token) ? json_decode((string)@file_get_contents($file), true) : null;
if(!is_array($stored) || (int)($stored['expires'] ?? 0) < time() || !hash_equals((string)($stored['hash'] ?? ''), hash('sha256', $token)))
{
    http_response_code(404);
    exit;
}
@unlink($file); // one use only

header('Content-Type: application/json');
header('Cache-Control: no-store');

if(($stored['mode'] ?? '') === 'reset')
{
    $cleared = function_exists('opcache_reset') && @opcache_reset();
    echo json_encode(['ok' => true, 'reset' => $cleared]);
    exit;
}

$answer = ['ok' => false];
try
{
    require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'init.php';

    $server = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'servers' . DIRECTORY_SEPARATOR . basename(__DIR__);
    if(($stored['mode'] ?? '') === 'upgrade')
    {
        // every PHP file of the new version must at least parse (WHMCS hides errors of a module's hooks.php, so a
        // broken file would otherwise pass unnoticed and fail later on some page)
        foreach([__DIR__, $server] as $root)
        {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
            foreach($files as $item)
            {
                if(substr($item->getFilename(), -4) !== '.php' || strpos($item->getPathname(), DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR) !== false)
                {
                    continue;
                }
                try
                {
                    token_get_all((string)file_get_contents($item->getPathname()), TOKEN_PARSE);
                }
                catch(\ParseError $e)
                {
                    throw new \RuntimeException('syntax error in ' . substr($item->getPathname(), strlen(dirname(__DIR__, 3)) + 1) . ' line ' . $e->getLine() . ': ' . $e->getMessage());
                }
            }
        }
    }

    // the module's entry files run here, so an error in them is caught (WHMCS swallows errors of hooks.php)
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Bootstrap.php';
    include __DIR__ . DIRECTORY_SEPARATOR . 'hooks.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'TSSGGSModule.php';
    if(is_file($server . DIRECTORY_SEPARATOR . 'TSSGGSModule.php'))
    {
        require_once $server . DIRECTORY_SEPARATOR . 'TSSGGSModule.php';
    }
    $version = (string)\ModulesGarden\TSSGGSModule\Core\Support\Facades\Config::get('configuration.version', '');

    if(($stored['mode'] ?? '') === 'upgrade')
    {
        // what WHMCS does when the version of the files changed: the addon's upgrade with the previous version, then
        // the new version stored - so it is not run again on the next admin page (running it twice is harmless too)
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'TSSGGSModule.php';
        $from = (string)($stored['from'] ?? '');
        if($from !== '' && function_exists('TSSGGSModule_upgrade'))
        {
            TSSGGSModule_upgrade(['version' => $from]);
        }
        \WHMCS\Database\Capsule::table('tbladdonmodules')->where('module', 'TSSGGSModule')->where('setting', 'version')->update(['value' => $version]);
    }

    $answer = ['ok' => true, 'version' => $version, 'php' => PHP_VERSION,
               'opcache_ok' => \ModulesGarden\TSSGGSModule\App\Libs\Update\Preflight::opcacheOk()];
}
catch(\Throwable $e)
{
    $answer = ['ok' => false, 'error' => get_class($e) . ': ' . $e->getMessage()];
}

echo json_encode($answer);
