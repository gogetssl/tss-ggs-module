<?php
// phpcs:ignoreFile

/**
 * The addon module's entry file: the functions WHMCS calls (Setup > Addon Modules, the admin page, the client page).
 * They only pass the call on: App\Kernel\Lifecycle (description, activate, deactivate, upgrade), App\Kernel\Kernel
 * (the admin pages, app/Pages/Admin) and App\ClientArea\SslPages (the client's certificate pages).
 */

if (!defined('WHMCS'))
{
    die('This file cannot be accessed directly');
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Kernel' . DIRECTORY_SEPARATOR . 'Boot.php';

/**
 * An install made before 1.4.0 lived in a folder of another name, and before 1.3.0 its tables were named after
 * that folder too. Both are moved over before anything else runs, so the schema finds the tables that are there
 * and WHMCS finds the products this module provisions.
 */
function TSSGGSModule_adoptAnOlderInstall()
{
    // older names first, then the database changes since schema.sql (App\Database\Schema)
    foreach (['\\TSSGGSModule\\App\\Libs\\Support\\TablePrefix', '\\TSSGGSModule\\App\\Libs\\Support\\ModuleRename', '\\TSSGGSModule\\App\\Database\\Schema'] as $step)
    {
        try
        {
            call_user_func([$step, 'migrate']);
        }
        catch (\Throwable $e)
        {
            // an install that cannot be moved over keeps working on what it has; the next run tries again
        }
    }
}

function TSSGGSModule_config()
{
    TSSGGSModule_adoptAnOlderInstall();

    return \TSSGGSModule\App\Kernel\Lifecycle::config();
}

function TSSGGSModule_activate()
{
    TSSGGSModule_adoptAnOlderInstall();

    return \TSSGGSModule\App\Kernel\Lifecycle::activate();
}

function TSSGGSModule_deactivate()
{
    return \TSSGGSModule\App\Kernel\Lifecycle::deactivate();
}

function TSSGGSModule_upgrade($params)
{
    TSSGGSModule_adoptAnOlderInstall();

    return \TSSGGSModule\App\Kernel\Lifecycle::upgrade((array)$params);
}

/** The admin area of the module: addonmodules.php?module=...&mod-page=<page>&mod-action=<action> */
function TSSGGSModule_output($params)
{
    \TSSGGSModule\App\Kernel\Kernel::run('admin');
}

if (defined('CLIENTAREA'))
{
    function TSSGGSModule_clientarea($params)
    {
        // Client's SSL certificates (summary filters) and "SSL Certificates by Domain" - plain Smarty pages
        return \TSSGGSModule\App\ClientArea\SslPages::render($_GET);
    }
}
