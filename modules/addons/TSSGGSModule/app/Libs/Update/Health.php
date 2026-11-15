<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Update;

use ModulesGarden\TSSGGSModule\App\Libs\Edition;

/**
 * Asks the module, in a separate request through the web server, whether it loads: modules/addons/<folder>/update-health.php.
 * That request runs WHMCS's init.php, which loads every active addon's hooks.php, so new code that fails on this
 * server fails there - before the update is kept - and not on the admin's next page.
 *
 * The request carries a one-time token (stored as a hash, valid 5 minutes): without it the endpoint answers nothing.
 */
class Health
{
    const SETTING = 'moduleUpdateHealth';

    /** The last answer of ping() (what the web server's PHP reported) */
    public static $lastAnswer = [];

    /** Clears the web server's OPcache from inside it (a command-line run can not) */
    public static function resetWebOpcache()
    {
        try
        {
            self::call('reset');
        }
        catch(\Throwable $e)
        {
        }
    }

    /** The running version answers: true, or why not */
    public static function ping()
    {
        try
        {
            $answer           = self::call('ping');
            self::$lastAnswer = $answer;

            return !empty($answer['ok']) ? true : (string)($answer['error'] ?? 'no answer');
        }
        catch(\Throwable $e)
        {
            return $e->getMessage();
        }
    }

    /**
     * After the swap: the new files run WHMCS's addon upgrade (new tables / columns) and report their version
     * @return array ['ok' => bool, 'version' => ..., 'error' => ...]
     */
    public static function upgradeAndCheck($expectedVersion, $fromVersion)
    {
        try
        {
            self::resetWebOpcache();
            $answer = self::call('upgrade', ['from' => $fromVersion]);
        }
        catch(\Throwable $e)
        {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
        if(empty($answer['ok']))
        {
            return ['ok' => false, 'error' => (string)($answer['error'] ?? 'the new version did not answer')];
        }
        if((string)($answer['version'] ?? '') !== (string)$expectedVersion)
        {
            return ['ok' => false, 'error' => 'the new files report version ' . ($answer['version'] ?? '?')];
        }

        return $answer;
    }

    protected static function call($mode, array $params = [])
    {
        $token = bin2hex(random_bytes(32));
        Workdir::ensure();
        file_put_contents(Workdir::path('health.json'), json_encode(['hash' => hash('sha256', $token), 'mode' => $mode, 'expires' => time() + 300] + $params));
        try
        {
            $base = rtrim((string)\WHMCS\Config\Setting::getValue('SystemURL'), '/');
            $url  = $base . '/modules/addons/' . rawurlencode(Edition::folder()) . '/update-health.php?token=' . $token;

            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT        => 120,
                CURLOPT_USERAGENT      => 'TSSGGSModule-Updater/' . State::installedVersion(),
            ]);
            $body   = curl_exec($curl);
            $status = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
            $error  = curl_error($curl);
            curl_close($curl);
        }
        finally
        {
            @unlink(Workdir::path('health.json'));
        }

        if($body === false)
        {
            throw new \RuntimeException($error ?: 'no connection');
        }
        $answer = json_decode((string)$body, true);
        if(!is_array($answer))
        {
            throw new \RuntimeException('HTTP ' . $status . ($status >= 500 ? ' (PHP error in the module)' : ''));
        }

        return $answer;
    }
}
