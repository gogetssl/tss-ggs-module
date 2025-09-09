<?php

namespace ModulesGarden\TSSGGSModule\Core\Helper;

use ModulesGarden\TSSGGSModule\Core\Http\JsonResponse;
use ModulesGarden\TSSGGSModule\Core\Http\RedirectResponse;
use ModulesGarden\TSSGGSModule\Core\Http\Response;
use ModulesGarden\TSSGGSModule\Core\ModuleConstants;
use ModulesGarden\TSSGGSModule\Core\ServiceLocator;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Session;
use ModulesGarden\TSSGGSModule\Core\UI\View;
use ModulesGarden\TSSGGSModule\Core\UI\ViewAjax;
use ModulesGarden\TSSGGSModule\Core\UI\ViewIntegrationAddon;

if (!function_exists('\ModulesGarden\TSSGGSModule\Core\Helper\response'))
{
    /**
     * @param array $data
     * @return Response
     */
    function response(array $data = [])
    {
        return Response::create()->setData($data);
    }
}

if (!function_exists('\ModulesGarden\TSSGGSModule\Core\Helper\redirect'))
{
    /**
     * @param array $data
     * @return JsonResponse
     */
    function redirect($controller = null, $action = null, array $params = [])
    {
        return RedirectResponse::createMG($controller, $action, $params);
    }
}


if (!function_exists('\ModulesGarden\TSSGGSModule\Core\Helper\sl'))
{
    /**
     * @param string $class
     * @param string|null $method
     * @return object
     * @deprecated - use make
     */
    function sl($class, $method = null)
    {
        $return = null;

        if ($class != null && $method == null)
        {
            $return = ServiceLocator::call($class);
        }
        elseif ($class != null && $method != null)
        {
            $return = ServiceLocator::call($class, $method);
        }

        return $return;
    }
}

if (!function_exists('\ModulesGarden\TSSGGSModule\Core\Helper\isAdmin'))
{
    /**
     * @return bool
     */
    function isAdmin(): bool
    {
        return defined('ADMINAREA') && Session::get('adminid');
    }
}

if (!function_exists('\ModulesGarden\TSSGGSModule\Core\Helper\getAdminDirName'))
{
    /**
     * @return string
     */
    function getAdminDirName()
    {
        $fileName = 'configuration.php';
        $filePath = ModuleConstants::getFullPathWhmcs();

        global $customadminpath;
        if (!$customadminpath && file_exists($filePath . DIRECTORY_SEPARATOR . $fileName))
        {
            include_once $filePath . DIRECTORY_SEPARATOR . $fileName;
        }

        if ($customadminpath && is_string($customadminpath))
        {
            return $customadminpath;
        }

        return 'admin';
    }
}

if (!function_exists('\ModulesGarden\TSSGGSModule\Core\Helper\view'))
{
    function view()
    {
        if (Request::get('ajax') && Request::get('namespace') != null && Request::get('namespace') != '' && Request::get('namespace') != 'undefined')
        {
            return new ViewAjax();
        }

        return new View();
    }
}

if (!function_exists('\ModulesGarden\TSSGGSModule\Core\Helper\viewIntegrationAddon'))
{
    /**
     * View Integration Addon Controler
     *
     * @return ViewIntegrationAddon
     */
    function viewIntegrationAddon()
    {
        if (Request::get('ajax') && Request::get('namespace') != null && Request::get('namespace') != '' && Request::get('namespace') != 'undefined')
        {
            return new ViewAjax();
        }

        return new ViewIntegrationAddon();
    }
}

if (!function_exists('\ModulesGarden\TSSGGSModule\Core\Helper\fire'))
{
    /**
     * @deprecated - use \ModulesGarden\TSSGGSModule\Core\fire
     */
    function fire($event)
    {
        return \ModulesGarden\TSSGGSModule\Core\fire($event);
    }
}
