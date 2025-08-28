<?php

namespace ModulesGarden\TTSGGSModule\Core\Hook\Integration;

use ModulesGarden\TTSGGSModule\Core\Http\AbstractClientController;
use ModulesGarden\TTSGGSModule\Core\Http\AbstractController;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;

class IntegrationChecker
{
    public static function isApplicable(Integration $integration, array $params = []):bool
    {
        return self::checkFileName($integration, $params) &&
               self::checkRequestParams($integration) &&
               self::checkCallback($integration);
    }

    protected static function checkFileName(Integration $integration, array $params = []):bool
    {
        return $integration->getRequiredFile() && $params['filename'] === $integration->getRequiredFile();
    }

    protected static function checkRequestParams(Integration $integration):bool
    {
        return self::checkCollection($integration->getRequiredRequestFields(), function ($paramKey) { return Request::get($paramKey); }) &&
               self::checkCollection($integration->getRequiredGetFields(), function ($paramKey) { return Request::query()->get($paramKey); }) &&
               self::checkCollection($integration->getRequiredPostFields(), function ($paramKey) { return Request::request()->get($paramKey); });
    }

    protected static function checkCallback(Integration $integration):bool
    {
        $integrationCallback = $integration->getControllerCallback();

        return is_subclass_of($integrationCallback->getController(), AbstractController::class) &&
               !is_subclass_of($integrationCallback->getController(), AbstractClientController::class) &&
               method_exists($integrationCallback->getController(), $integrationCallback->getAction());
    }

    protected static function checkCollection(array $params, callable $getCallback):bool
    {
        foreach ($params as $rKey => $rParam)
        {
            if (is_array($rParam))
            {
                $found = false;
                foreach ($rParam as $irParam)
                {
                    if (call_user_func($getCallback, $rKey) === $irParam)
                    {
                        $found = true;
                        break;
                    }
                }
                if (!$found)
                {
                    return false;
                }
            }
            elseif (call_user_func($getCallback, $rKey) !== $rParam)
            {
                return false;
            }
        }

        return true;
    }
}