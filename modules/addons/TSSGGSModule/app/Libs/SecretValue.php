<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

/**
 * Secrets kept in the database (EAB HMAC keys, SSO links) and secrets that must never reach the module log.
 * Stored values carry a prefix, so rows written before the encryption (or by the migration) are still readable and are
 * encrypted the next time they are saved.
 */
class SecretValue
{
    const PREFIX = 'enc:';

    /** names of fields whose value is replaced in logs */
    const SENSITIVE = ['eab_hmac_key', 'eab_mac_key', 'hmac_key', 'password', 'passwd', 'secret', 'api_key', 'apikey', 'authtoken', 'auth_token', 'token', 'private_key', 'privatekey',
                       'login_sso_link', 'manage_sso_link', 'sso_link', 'sso_url', 'partnercode', 'testpartnercode', 'testauthtoken', 'authorization'];

    public static function seal($value)
    {
        $value = (string)$value;
        if($value === '' || strpos($value, self::PREFIX) === 0)
        {
            return $value;
        }

        return self::PREFIX . \encrypt($value);
    }

    public static function open($value)
    {
        $value = (string)$value;
        if(strpos($value, self::PREFIX) !== 0)
        {
            return $value;
        }

        return (string)\decrypt(substr($value, strlen(self::PREFIX)));
    }

    /**
     * A copy of a request / response (string, JSON text or array) with the sensitive values masked, for the module log
     */
    public static function redact($data)
    {
        if(is_array($data))
        {
            foreach($data as $key => $value)
            {
                $data[$key] = in_array(strtolower((string)$key), self::SENSITIVE, true) && is_scalar($value) && $value !== '' ? '***' : self::redact($value);
            }

            return $data;
        }
        if(!is_string($data) || $data === '')
        {
            return $data;
        }

        // HTTP request headers
        $data = preg_replace('/^(Authorization|Cookie|X-[A-Za-z-]*(?:Token|Key|Secret|Auth)[A-Za-z-]*|[A-Za-z-]*Token|Partner[A-Za-z-]*)\s*:.*$/im', '$1: ***', $data);
        $names = implode('|', array_map('preg_quote', self::SENSITIVE));
        // JSON text and form-encoded text
        $data = preg_replace('/("(?:' . $names . ')"\s*:\s*)"(?:[^"\\\\]|\\\\.)*"/i', '$1"***"', $data);
        $data = preg_replace('/((?:^|[?&\s])(?:' . $names . ')=)[^&\s]*/im', '$1***', $data);

        // PEM private keys wherever they appear (JSON escapes the line breaks as \n)
        return preg_replace('/-----BEGIN [A-Z ]*PRIVATE KEY-----.*?-----END [A-Z ]*PRIVATE KEY-----/s', '-----PRIVATE KEY REMOVED-----', $data);
    }
}
