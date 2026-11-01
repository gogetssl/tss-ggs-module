<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

/**
 * Private keys generated for the client in the browser (CSR generator, manual enrollment) are never put in a URL or
 * handed out as ciphertext: the key waits in the client's session under a random handle and is downloaded with a POST
 * (token required) for the same service, within an hour.
 */
class PrivateKeyDownload
{
    const SESSION = 'tssggs_private_keys';
    const TTL     = 3600;
    const MAX     = 5;

    /**
     * @return string handle for fetch()
     */
    public static function store($serviceId, $pem)
    {
        $keys = self::fresh();
        $handle = bin2hex(random_bytes(16));
        $keys[$handle] = ['service' => (int)$serviceId, 'key' => \encrypt((string)$pem), 'time' => time()];
        $_SESSION[self::SESSION] = array_slice($keys, -self::MAX, null, true);

        return $handle;
    }

    /**
     * @return string|null the PEM, null when the handle is unknown, expired or belongs to another service
     */
    public static function fetch($serviceId, $handle)
    {
        $keys = self::fresh();
        if(!isset($keys[$handle]) || (int)$keys[$handle]['service'] !== (int)$serviceId)
        {
            return null;
        }
        $pem = \decrypt($keys[$handle]['key']);

        return is_string($pem) && strpos($pem, 'PRIVATE KEY-----') !== false ? $pem : null;
    }

    protected static function fresh()
    {
        $keys = isset($_SESSION[self::SESSION]) && is_array($_SESSION[self::SESSION]) ? $_SESSION[self::SESSION] : [];

        return array_filter($keys, function($row) {
            return isset($row['time']) && $row['time'] > time() - self::TTL;
        });
    }
}
