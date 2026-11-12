<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

/**
 * WHMCS CSRF token check for the module's own forms and links. Pages print generate_token('plain'), which is
 * derived from the session value (not the raw $_SESSION['tkval']), so the posted value is compared with it.
 */
class CsrfToken
{
    public static function valid($token)
    {
        $token = (string)$token;
        if($token === '')
        {
            return false;
        }

        $expected = function_exists('generate_token') ? (string)\generate_token('plain') : '';
        $raw      = (string)($_SESSION['tkval'] ?? '');

        return ($expected !== '' && hash_equals($expected, $token)) || ($raw !== '' && hash_equals($raw, $token));
    }
}
