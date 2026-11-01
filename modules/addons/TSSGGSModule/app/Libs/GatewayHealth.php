<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

/**
 * Connectivity check of the configured gateways (products request), cached in the admin session for 10 minutes
 */
class GatewayHealth
{
    const CACHE_SECONDS = 600;

    /**
     * [vendor => ['ok' => bool, 'ms' => int, 'error' => string, 'checked' => timestamp]]
     */
    public static function check($force = false)
    {
        $cached = $_SESSION['tssggs_gateway_health'] ?? null;
        if(!$force && is_array($cached) && $cached['time'] > time() - self::CACHE_SECONDS)
        {
            return $cached['result'];
        }

        $result = [];
        foreach(Helpers::getConfiguredApis() as $vendor => $api)
        {
            $start = microtime(true);
            try
            {
                $api->getProducts();
                $result[$vendor] = ['ok' => true, 'error' => ''];
            }
            catch(\Throwable $e)
            {
                $result[$vendor] = ['ok' => false, 'error' => $e->getMessage()];
            }
            $result[$vendor]['ms']      = (int)round((microtime(true) - $start) * 1000);
            $result[$vendor]['checked'] = time();
        }

        $_SESSION['tssggs_gateway_health'] = ['time' => time(), 'result' => $result];

        return $result;
    }
}
