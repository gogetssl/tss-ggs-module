<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Errors;

use ModulesGarden\TTSGGSModule\Core\HandlerError\Exceptions\Exception;
use function logModuleCall;

/**
 * Register Error in WHMCS Module Log
 */
class Register extends Exception
{
    protected $exception = null;

    /**
     * Register Exception in WHMCS Module Log
     *
     *
     * @param Exception $exc
     */
    public static function register($exc)
    {
        if (!self::isExceptionLogable($exc))
        {
            return;
        }

        $debug = var_export($exc, true);

        logModuleCall('Error', __NAMESPACE__, [
            'message' => $exc->getMessage(),
            'code'    => $exc->getCode(),
            'token'   => self::getToken($exc),
        ],
            $debug, 0, 0);
    }

    /**
     * Checks if the exception can be logged
     * @return bool
     */
    public static function isExceptionLogable($exception = null)
    {
        if (method_exists($exception, 'isLogable'))
        {
            return $exception->isLogable();
        }

        return false;
    }

    /**
     * Returns an error token string
     * @return type string
     */
    public static function getToken($exception)
    {
        $token = 'Unknow Token';

        if (method_exists($exception, 'getToken'))
        {
            $token = $exception->getToken();
        }

        return $token;
    }
}
