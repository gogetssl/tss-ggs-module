<?php
// phpcs:ignoreFile

use ModulesGarden\TTSGGSModule\Core\App\AppContext;

if (!defined('WHMCS'))
{
    die('This file cannot be accessed directly');
}

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'App' . DIRECTORY_SEPARATOR . 'AppContext.php';

function TTSGGSModule_CreateAccount(array $params)
{
    #MGLICENSE_CHECK_RETURN#

    return (new AppContext())->runServerModule(__FUNCTION__, $params);
}

function TTSGGSModule_SuspendAccount(array $params)
{
    #MGLICENSE_CHECK_RETURN#

    return (new AppContext())->runServerModule(__FUNCTION__, $params);
}

function TTSGGSModule_UnsuspendAccount(array $params)
{
    #MGLICENSE_CHECK_RETURN#

    return (new AppContext())->runServerModule(__FUNCTION__, $params);
}

function TTSGGSModule_TerminateAccount(array $params)
{
    #MGLICENSE_CHECK_RETURN#

    return (new AppContext())->runServerModule(__FUNCTION__, $params);
}

function TTSGGSModule_ChangePassword(array $params)
{
    #MGLICENSE_CHECK_RETURN#

    return (new AppContext())->runServerModule(__FUNCTION__, $params);
}

function TTSGGSModule_ChangePackage(array $params)
{
    #MGLICENSE_CHECK_RETURN#

    return (new AppContext())->runServerModule(__FUNCTION__, $params);
}

function TTSGGSModule_TestConnection(array $params)
{
    try
    {
        #MGLICENSE_CHECK_THROW_EXCEPTION#
    }
    catch (\Exception $ex)
    {
        return [
            'error' => $ex->getMessage()
        ];
    }

    return (new AppContext())->runServerModule(__FUNCTION__, $params);
}

function TTSGGSModule_UsageUpdate(array $params)
{
    return (new AppContext())->runServerModule(__FUNCTION__, $params);
}

function TTSGGSModule_ConfigOptions($params = [])
{
    return (new AppContext())->runServerModule(__FUNCTION__, $params);
}

function TTSGGSModule_ServiceSingleSignon($params)
{
    return (new AppContext())->runServerModule(__FUNCTION__, $params);
}

function TTSGGSModule_MetaData()
{
    return (new AppContext())->runServerModule(__FUNCTION__, []);
}

function TTSGGSModule_AdminSingleSignOn($params)
{
    return (new AppContext())->runServerModule(__FUNCTION__, $params);
}

function TTSGGSModule_AdminServicesTabFields($params)
{
    return (new AppContext())->runServerModule(__FUNCTION__, $params);
}

if (defined('CLIENTAREA'))
{
    function TTSGGSModule_ClientArea($params)
    {
        #MGLICENSE_CHECK_RETURN#

        return (new AppContext())->runServerModule(__FUNCTION__, $params);
    }
}