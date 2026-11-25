<?php

namespace TSSGGSModule\App\Libs\Update;

/**
 * HTTPS for the updater: certificate checks always on, redirects followed by hand and only to the hosts releases are
 * served from (GitHub and its download hosts, our CDN), downloads streamed to a file with a size cap.
 * Sends nothing about the install but the module edition and version (User-Agent).
 */
class Http
{
    const HOSTS = ['api.github.com', 'github.com', 'objects.githubusercontent.com', 'release-assets.githubusercontent.com', 'ggssl.com'];

    const MAX_REDIRECTS = 5;

    /** Extra host allowed for a test release source (WHMCS configuration.php: TSSGGS_UPDATE_SOURCE) */
    protected static function allowed($url)
    {
        $parts = parse_url((string)$url);
        if(($parts['scheme'] ?? '') !== 'https' || empty($parts['host']))
        {
            return false;
        }
        $hosts = self::HOSTS;
        if(defined('TSSGGS_UPDATE_SOURCE'))
        {
            $hosts[] = (string)parse_url((string)constant('TSSGGS_UPDATE_SOURCE'), PHP_URL_HOST);
        }

        return in_array(strtolower($parts['host']), $hosts, true);
    }

    /**
     * GET a small resource into memory
     * @return array [status, body, headers]
     */
    public static function get($url, array $headers = [], $maxBytes = 5242880)
    {
        return self::request($url, $headers, null, $maxBytes);
    }

    /**
     * GET into a file (written as it arrives, never more than $maxBytes)
     * @return array [status, '', headers]
     */
    public static function download($url, $file, $maxBytes)
    {
        $handle = fopen($file, 'wb');
        if(!$handle)
        {
            throw new \RuntimeException('Cannot write the download file');
        }
        try
        {
            return self::request($url, [], $handle, $maxBytes);
        }
        finally
        {
            fclose($handle);
        }
    }

    protected static function request($url, array $headers, $handle, $maxBytes)
    {
        for($hop = 0; $hop <= self::MAX_REDIRECTS; $hop++)
        {
            if(!self::allowed($url))
            {
                throw new \RuntimeException('Release address not allowed: ' . parse_url((string)$url, PHP_URL_HOST));
            }
            if($handle)
            {
                ftruncate($handle, 0);
                rewind($handle);
            }

            $body     = '';
            $received = 0;
            $response = [];
            $curl     = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_PROTOCOLS      => CURLPROTO_HTTPS,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT        => $handle ? 300 : 20,
                CURLOPT_USERAGENT      => 'TSSGGSModule-Updater/' . State::installedVersion(),
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_HEADERFUNCTION => function($curl, $line) use (&$response) {
                    $parts = explode(':', $line, 2);
                    if(count($parts) === 2)
                    {
                        $response[strtolower(trim($parts[0]))] = trim($parts[1]);
                    }

                    return strlen($line);
                },
                CURLOPT_WRITEFUNCTION  => function($curl, $data) use (&$body, &$received, $handle, $maxBytes) {
                    $received += strlen($data);
                    if($received > $maxBytes)
                    {
                        return 0; // aborts the transfer
                    }
                    $handle ? fwrite($handle, $data) : $body .= $data;

                    return strlen($data);
                },
            ]);
            $ok     = curl_exec($curl);
            $status = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
            $error  = curl_error($curl);
            curl_close($curl);

            if($received > $maxBytes)
            {
                throw new \RuntimeException('Release file larger than expected');
            }
            if(in_array($status, [301, 302, 303, 307, 308], true) && !empty($response['location']))
            {
                $url     = $response['location'];
                $headers = []; // nothing (no conditional request header) follows to the download host
                continue;
            }
            if($ok === false)
            {
                throw new \RuntimeException('Download failed: ' . $error);
            }

            return [$status, $body, $response];
        }

        throw new \RuntimeException('Too many redirects');
    }
}
