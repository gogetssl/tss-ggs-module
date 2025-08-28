<?php

namespace ModulesGarden\TTSGGSModule\Core\Security\CsrfToken;

use ModulesGarden\TTSGGSModule\Core\Session\Session;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Config\Config;

class CsrfToken
{
    protected static $validityTime = 300;

    public static function generate(string $name): string
    {
        $startTime = (int)(time() / self::$validityTime);
        $endTime   = $startTime + 1;

        return self::token($name, $startTime, $endTime);
    }

    public static function validate(string $name, string $token): void
    {
        [$prefix, $startTime, $endTime] = explode(':', $token);
        $time = (int)(time() / self::$validityTime);

        if ($time < $startTime || $time > $endTime)
        {
            throw new \Exception('Outdated CSRF token. Refresh page.');
        }


        if (self::token($name, $startTime, $endTime) != $token)
        {
            throw new \Exception('Invalid CSRF token');
        }
    }


    protected static function token(string $name, int $startTime, int $endTime): string
    {
        $session = new Session();
        $config  = new Config();
        global $cc_encryption_hash;

        $uid     = $session->get('uid');
        $adminId = $session->get('adminid');
        $domain  = $config->get('Domain');

        return sha1($name . $cc_encryption_hash . $uid . $adminId . $domain . $endTime . $startTime . $endTime) . ':' . $startTime . ':' . $endTime;
    }
}