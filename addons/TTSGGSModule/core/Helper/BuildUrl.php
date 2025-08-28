<?php

namespace ModulesGarden\TTSGGSModule\Core\Helper;

use ModulesGarden\TTSGGSModule\Core\ModuleConstants;

/**
 * Description of BuildUrl
 */
class BuildUrl
{
    protected const RESERVED_PORTS = [80, 443];

    public static function currentUrl()
    {
        $qs = html_entity_decode($_SERVER['QUERY_STRING']);

        return self::fullUrl() . ($qs ? '?' . $qs : '');
    }

    public static function getBaseUrl(): string
    {
        $scheme = self::getScheme();
        $host   = self::getHost();
        $suffix = self::getUrlSuffix();
        $port   = self::getPortPrefixed();

        return "{$scheme}://{$host}{$port}{$suffix}/";
    }

    public static function fullUrl(): string
    {
        $scheme = self::getScheme();
        $host   = self::getHost();
        $port   = self::getPortPrefixed();

        return "{$scheme}://{$host}{$port}{$_SERVER['PHP_SELF']}";
    }

    public static function rootUrl(): string
    {
        $scheme     = self::getScheme();
        $host       = self::getHost();
        $hostPath   = self::getHostPath();
        $port       = self::getPortPrefixed();

        return "{$scheme}://{$host}{$port}{$hostPath}/";
    }

    public static function getScheme(): string
    {
        return self::getVisitorScheme() ?? self::getBaseScheme();
    }

    public static function getHost(): string
    {
        $host = $GLOBALS['CONFIG']['SystemURL'] ?: $_SERVER['HTTP_HOST'];
        $url  = \parse_url($host);

        return $url['host'] ?? '';
    }

    public static function getHostPath(): string
    {
        $host = $GLOBALS['CONFIG']['SystemURL'] ?: $_SERVER['HTTP_HOST'];
        $url  = \parse_url($host);

        return $url['path'] ?? '';
    }

    public static function getBaseScheme(): string
    {
        return (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') ? 'http' : 'https';
    }

    public static function getVisitorScheme()
    {
        if (empty($_SERVER['HTTP_CF_VISITOR']))
        {
            return null;
        }

        $visitorParams = (array)json_decode(html_entity_decode($_SERVER['HTTP_CF_VISITOR']));
        return $visitorParams['scheme'] ?? null;
    }

    public static function getUrlSuffix(): string
    {
        $suffix = $_SERVER['PHP_SELF'] ?: '';
        $suffix = explode('/', $suffix);
        array_pop($suffix);
        return implode('/', $suffix);
    }

    public static function getPort(): ?string
    {
        $host = $_SERVER['HTTP_HOST'] ?: $GLOBALS['CONFIG']['SystemURL'];

        $url = \parse_url($host);

        return $url['port'];
    }

    public static function getAssetsURL(...$path): string
    {
        global $CONFIG;

        return $CONFIG['SystemURL'] . '/modules/' . ModuleConstants::getModuleType() . '/' . ModuleConstants::getModuleName() . '/resources/assets/' . implode('/', $path);
    }

    public static function getPackagesURL(...$path): string
    {
        global $CONFIG;

        return $CONFIG['SystemURL'] . '/modules/' . ModuleConstants::getModuleType() . '/' . ModuleConstants::getModuleName() . "/packages/" . implode('/', $path);
    }

    public static function getComponentsURL(...$path): string
    {
        global $CONFIG;

        return $CONFIG['SystemURL'] . '/modules/' . ModuleConstants::getModuleType() . '/' . ModuleConstants::getModuleName() . "/components/" . implode('/', $path);
    }

    public static function getNewUrl($protocol = 'http', $host = 'modulesgarden.com', $params = []): string
    {
        $url = "{$protocol}://{$host}";
        if ($params)
        {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    //@todo
    public static function getUrl($controller = null, $action = null, array $params = [], $isFullUrl = true)
    {
        if (isAdmin())
        {
            $url = 'addonmodules.php?module=' . ModuleConstants::getModuleName();
        }
        else
        {
            $url = 'index.php?m=' . ModuleConstants::getModuleName();
        }

        if ($controller)
        {
            $url .= '&mg-page=' . $controller;
            if ($action)
            {
                $url .= '&mg-action=' . $action;
            }

            if ($params)
            {
                $url .= '&' . http_build_query($params);
            }
        }

        if ($isFullUrl)
        {
            $baseUrl = self::getBaseUrl();
            $url     = $baseUrl . $url;
        }

        return $url;
    }

    public static function getValidPort(): ?string
    {
        $port = self::getPort();

        return in_array($port, self::RESERVED_PORTS) ? null : $port;
    }

    protected static function getPortPrefixed(): string
    {
        $port = self::getValidPort();

        return $port ? ":{$port}" : "";
    }
}
