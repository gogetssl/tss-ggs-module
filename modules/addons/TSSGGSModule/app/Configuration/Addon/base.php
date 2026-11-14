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
    return (new AppContext())->runAddonModule(__FUNCTION__, []);
}

/**
 * Tables of an install made before 1.3.0 still carry the folder name; they take the edition's name before
 * anything else runs, so the schema below finds them instead of creating a second, empty set.
 */
function TSSGGSModule_renameTablesOfOlderInstalls()
{
    require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'Libs' . DIRECTORY_SEPARATOR . 'Edition.php';
    require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'Libs' . DIRECTORY_SEPARATOR . 'TablePrefix.php';

    try
    {
        \ModulesGarden\TSSGGSModule\App\Libs\TablePrefix::migrate();
    }
    catch (\Throwable $e)
    {
        // an install that cannot rename its tables keeps working on the ones it has
    }
}

function TSSGGSModule_activate()
{
    TSSGGSModule_renameTablesOfOlderInstalls();

    return (new AppContext())->runAddonModule(__FUNCTION__, []);
}

function TSSGGSModule_deactivate()
{
    return (new AppContext())->runAddonModule(__FUNCTION__, []);
}

function TSSGGSModule_upgrade($params)
{
    TSSGGSModule_renameTablesOfOlderInstalls();

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
