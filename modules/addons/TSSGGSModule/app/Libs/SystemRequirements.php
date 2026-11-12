<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use WHMCS\Database\Capsule;

/**
 * Server checks shown in the installation wizard (step 2).
 * Every check returns: name, required (shown requirement), found (detected value), status and blocking.
 * A failed blocking check stops the wizard; a non-blocking one is only a warning.
 */
class SystemRequirements
{
    const STATUS_PASSED  = 'passed';
    const STATUS_FAILED  = 'failed';
    const STATUS_WARNING = 'warning';

    const MIN_PHP   = '8.1.0';
    const MIN_WHMCS = '8.6.0';

    const EXTENSIONS = ['curl', 'openssl', 'json', 'mbstring', 'pdo_mysql'];

    /**
     * Folders the module writes to, relative to the addon root
     */
    const STORAGE_DIRS = ['storage', 'storage/app', 'storage/crons', 'storage/logs'];

    const GATEWAY_TIMEOUT = 5;

    public function check(): array
    {
        return array_merge(
            [$this->checkPhp(), $this->checkWhmcs()],
            $this->checkExtensions(),
            [$this->checkStorage(), $this->checkGateway()]
        );
    }

    public static function hasBlockingFailure(array $checks): bool
    {
        foreach($checks as $check)
        {
            if($check['blocking'] && $check['status'] === self::STATUS_FAILED)
            {
                return true;
            }
        }

        return false;
    }

    protected function checkPhp(): array
    {
        return $this->result('php', '>= ' . self::MIN_PHP, PHP_VERSION, version_compare(PHP_VERSION, self::MIN_PHP, '>='));
    }

    protected function checkWhmcs(): array
    {
        $version = (string)Capsule::table('tblconfiguration')->where('setting', 'Version')->value('value');
        // "8.13.1-release.1", "9.0.0-rc.1" -> numeric part only
        $numeric = preg_replace('/[^0-9.].*$/', '', $version);

        return $this->result('whmcs', '>= ' . self::MIN_WHMCS, $version !== '' ? $version : '-', $numeric !== '' && version_compare($numeric, self::MIN_WHMCS, '>='));
    }

    protected function checkExtensions(): array
    {
        $checks = [];
        foreach(self::EXTENSIONS as $extension)
        {
            $loaded   = extension_loaded($extension);
            $checks[] = $this->result('ext_' . $extension, 'loaded', $loaded ? 'loaded' : 'missing', $loaded);
        }

        return $checks;
    }

    protected function checkStorage(): array
    {
        $root     = dirname(__DIR__, 2);
        $problems = [];
        foreach(self::STORAGE_DIRS as $dir)
        {
            $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $dir);
            if(!is_dir($path))
            {
                $problems[] = $dir . ' missing';
            }
            elseif(!is_writable($path))
            {
                $problems[] = $dir . ' not writable';
            }
        }

        // One row for all storage folders; the failed ones are named in "found"
        $result = $this->result('storage', 'writable', $problems ? implode(', ', $problems) : 'writable', !$problems, true, $root . DIRECTORY_SEPARATOR . 'storage');
        if($problems)
        {
            $result['command'] = self::storageCommand($root . DIRECTORY_SEPARATOR . 'storage');
        }

        return $result;
    }

    /**
     * Shell command that makes the storage folder writable for the user PHP runs as: its group gets write access
     * (the files keep their owner, so FTP / deploy tools still work). Without posix: plain 775.
     */
    public static function storageCommand(string $path): string
    {
        $path = escapeshellarg($path);
        if(function_exists('posix_getegid') && function_exists('posix_getgrgid'))
        {
            $group = posix_getgrgid(posix_getegid());
            if(!empty($group['name']))
            {
                return 'chgrp -R ' . escapeshellarg($group['name']) . ' ' . $path . ' && chmod -R g+rwX ' . $path;
            }
        }

        return 'chmod -R 775 ' . $path;
    }

    /**
     * Only tests that the gateway answers over HTTPS - credentials are checked in step 4.
     * Any HTTP response counts as reachable; a connection or TLS error does not. Not blocking: the
     * gateway may be briefly unavailable, and the API connection test in step 4 is decisive.
     */
    protected function checkGateway(): array
    {
        $url = SSLTrustCenterApi::API_URL;

        if(!function_exists('curl_init'))
        {
            return $this->result('gateway', 'reachable', 'not tested (cURL missing)', false, false, $url);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_NOBODY         => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => self::GATEWAY_TIMEOUT,
            CURLOPT_TIMEOUT        => self::GATEWAY_TIMEOUT,
        ]);
        curl_exec($ch);
        $code  = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        return $this->result('gateway', 'reachable', $code > 0 ? 'HTTP ' . $code : ($error ?: 'no response'), $code > 0, false, $url);
    }

    protected function result(string $key, string $required, string $found, bool $passed, bool $blocking = true, string $subject = ''): array
    {
        return [
            'key'      => $key,
            'subject'  => $subject,
            'required' => $required,
            'found'    => $found,
            'status'   => $passed ? self::STATUS_PASSED : ($blocking ? self::STATUS_FAILED : self::STATUS_WARNING),
            'blocking' => $blocking,
        ];
    }
}
