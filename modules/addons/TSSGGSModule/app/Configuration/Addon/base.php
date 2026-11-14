<?php
// phpcs:ignoreFile

use ModulesGarden\TSSGGSModule\Core\App\AppContext;

if (!defined('WHMCS'))
{
    die('This file cannot be accessed directly');
}

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'App' . DIRECTORY_SEPARATOR . 'AppContext.php';

function TSSGGSModule_config()
{
    TSSGGSModule_adoptAnOlderInstall();

    return (new AppContext())->runAddonModule(__FUNCTION__, []);
}

/**
 * An install made before 1.4.0 lived in a folder of another name, and before 1.3.0 its tables were named after
 * that folder too. Both are moved over before anything else runs, so the schema finds the tables that are there
 * and WHMCS finds the products this module provisions.
 */
function TSSGGSModule_adoptAnOlderInstall()
{
    $libs = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'Libs' . DIRECTORY_SEPARATOR;
    require_once $libs . 'Edition.php';
    require_once $libs . 'TablePrefix.php';
    require_once $libs . 'ModuleRename.php';
    require_once $libs . 'SchemaIndexes.php';

    foreach (['TablePrefix', 'ModuleRename', 'SchemaIndexes'] as $step)
    {
        try
        {
            call_user_func(['\\ModulesGarden\\TSSGGSModule\\App\\Libs\\' . $step, 'migrate']);
        }
        catch (\Throwable $e)
        {
            // an install that cannot be moved over keeps working on what it has; the next run tries again
        }
    }
}

function TSSGGSModule_activate()
{
    TSSGGSModule_adoptAnOlderInstall();

    return (new AppContext())->runAddonModule(__FUNCTION__, []);
}

function TSSGGSModule_deactivate()
{
    return (new AppContext())->runAddonModule(__FUNCTION__, []);
}

function TSSGGSModule_upgrade($params)
{
    TSSGGSModule_adoptAnOlderInstall();

    return (new AppContext())->runAddonModule(__FUNCTION__, $params);
}

function TSSGGSModule_output($params)
{
    #MGLICENSE_CHECK_ECHO_AND_RETURN_MESSAGE#

    $html = (new AppContext())->runAddonModule(__FUNCTION__, $params);

    if (is_array($html))
    {
        echo json_encode($html);
    }
    elseif ($html)
    {
        echo $html;
    }
}

if (defined('CLIENTAREA'))
{
    function TSSGGSModule_clientarea($params)
    {
        #MGLICENSE_CHECK_ECHO_AND_RETURN_MESSAGE#

        // Client's SSL certificates (summary filters) and "SSL Certificates by Domain" - plain Smarty pages
        require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Bootstrap.php';

        return \ModulesGarden\TSSGGSModule\App\ClientArea\SslPages::render($_GET);
    }
}
