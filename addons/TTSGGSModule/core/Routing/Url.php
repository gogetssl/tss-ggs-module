<?php

namespace ModulesGarden\TTSGGSModule\Core\Routing;

use ModulesGarden\TTSGGSModule\Core\Helper\BuildUrl;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use function ModulesGarden\TTSGGSModule\Core\Helper\isAdmin;

class Url
{
    public static function adminarea(string $path, array $parameters = []): string
    {
        global $customadminpath;
        $dir = $customadminpath ?: 'admin';

        return self::make($dir . '/' . $path, $parameters);
    }

    public static function clientarea(string $path, array $parameters = []): string
    {
        return self::make($path, $parameters);
    }

    /**
     * Generate URL to controller@method with provided parameters
     * @param string $route
     * @param array $parameters
     * @param string $level
     * @return string
     */
    public static function route(string $route = '', array $parameters = [], string $level = ModuleConstants::LEVEL_AUTO): string
    {
        [$controller, $action] = explode('@', $route);

        if ($controller)
        {
            $parameters[ModuleConstants::CONTROLLER_PAGE_PARAMETER] = $controller;
        }

        if ($action)
        {
            $parameters[ModuleConstants::CONTROLLER_ACTION_PARAMETER] = $action;
        }

        $level = $level == ModuleConstants::LEVEL_AUTO ? (isAdmin() ? ModuleConstants::LEVEL_ADMIN : ModuleConstants::LEVEL_CLIENT) : ($level);

        if ($level == ModuleConstants::LEVEL_CLIENT)
        {
            $parameters['m'] = ModuleConstants::getModuleName();

            return self::clientarea('index.php', $parameters);
        }
        else
        {
            $parameters['module'] = ModuleConstants::getModuleName();

            return self::adminarea('addonmodules.php', $parameters);
        }
    }

    public static function make(string $path, array $parameters = []): string
    {
        return BuildUrl::rootUrl() . $path . ($parameters ? '?' . http_build_query($parameters) : '');
    }

    public static function generateUrl(string $baseUrl, array $params = []): string
    {
        $queryString = http_build_query($params, '', '&');

        return $baseUrl . '?' . $queryString;
    }

    public static function getAdminFolderPath(): string
    {
        $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '';

        return rtrim(dirname($scriptPath), '/') . '/';
    }
}