<?php

namespace TSSGGSModule\App\Libs\Update;

use TSSGGSModule\App\Libs\Support\Edition;
use TSSGGSModule\App\Kernel\Kernel;

/**
 * Finds the newest release and proves it is ours.
 *
 * Every release carries release.json (version, minimum PHP / WHMCS, per edition: the ZIP's name, size and SHA-256 and
 * the SHA-256 of every file in it) and release.json.sig ({"key": "<key id>", "sig": "<base64 Ed25519 signature of
 * release.json's bytes>"}). Nothing from a release is used before that signature checks out against a key in
 * app/Config/updateKeys.php.
 *
 * Sources, in order: the GitHub release of the edition's distribution repository, then the copy on our CDN.
 * A test install can use another source (WHMCS configuration.php: TSSGGS_UPDATE_SOURCE = a base URL holding
 * release.json, release.json.sig and the ZIPs).
 */
class Release
{
    const CHECK_EVERY   = 86400;
    const MANIFEST_MAX  = 8388608;   // release.json with the file lists of both editions
    const ZIP_MAX       = 104857600; // 100 MB

    /** Trusted public keys: key id => raw 32-byte key */
    public static function keys()
    {
        $keys = [];
        $file = Kernel::path('app', 'Config', 'updateKeys.php');
        foreach((array)(is_file($file) ? include $file : []) as $id => $key)
        {
            $raw = base64_decode((string)$key, true);
            if($raw !== false && strlen($raw) === 32)
            {
                $keys[(string)$id] = $raw;
            }
        }
        if(defined('TSSGGS_UPDATE_TEST_KEY'))
        {
            $raw = base64_decode((string)constant('TSSGGS_UPDATE_TEST_KEY'), true);
            if($raw !== false && strlen($raw) === 32)
            {
                $keys['test'] = $raw;
            }
        }
        foreach((array)(State::get()['revokedKeys'] ?? []) as $revoked)
        {
            unset($keys[(string)$revoked]);
        }

        return $keys;
    }

    public static function canVerify()
    {
        return function_exists('sodium_crypto_sign_verify_detached') && self::keys();
    }

    /**
     * Looks for a newer release (at most once a day unless $force) and stores what it found
     * @return array the stored state
     */
    public static function check($force = false)
    {
        $state = State::get();
        if(!$force && time() - (int)($state['checkedAt'] ?? 0) < self::CHECK_EVERY)
        {
            return $state;
        }

        $state['checkedAt'] = time();
        $state['error']     = '';
        try
        {
            $found = self::latest($state);
            $state = array_merge($state, $found);
        }
        catch(\Throwable $e)
        {
            // what was verified before no longer counts
            $state['error']    = $e->getMessage();
            $state['verified'] = false;
        }
        State::set($state);

        return $state;
    }

    /** Is the stored release newer than the files that run now? */
    public static function available(array $state = null)
    {
        $state = $state ?? State::get();

        return !empty($state['version']) && version_compare((string)$state['version'], State::installedVersion(), '>');
    }

    /**
     * The newest release: version, notes, release page and - when it can be verified - the verified manifest of this
     * edition. Only the small "latest" record is read every day; release.json only when a newer version shows up.
     */
    protected static function latest(array $state)
    {
        $errors = [];
        foreach(self::sources() as $source)
        {
            try
            {
                $info = $source['type'] === 'github' ? self::githubLatest($source, $state) : self::cdnLatest($source);
                if(!$info)
                {
                    continue;
                }
                $found = ['version' => $info['version'], 'page' => $info['page'], 'source' => $source['name'], 'etag' => $info['etag'] ?? ($state['etag'] ?? ''),
                          'manifestUrl' => $info['manifestUrl'], 'signatureUrl' => $info['signatureUrl'], 'assetBase' => $info['assetBase']];
                if(version_compare($info['version'], State::installedVersion(), '<='))
                {
                    return $found + ['verified' => false, 'notes' => '', 'zipUrl' => ''];
                }
                // a newer release: its signed manifest is fetched and verified now, so the dashboard only ever offers
                // what an install would accept. The same version already verified is not fetched again.
                if(!self::canVerify())
                {
                    return $found + ['verified' => false, 'notes' => '', 'zipUrl' => ''];
                }
                $stored = ($state['version'] ?? '') === $info['version'] && !empty($state['verified']) ? Workdir::manifest() : null;
                if(!$stored)
                {
                    list($body, $signature) = self::fetchManifest($info['manifestUrl'], $info['signatureUrl']);
                    $stored = self::verify($body, $signature);
                    if($stored['version'] !== $info['version'])
                    {
                        throw new \RuntimeException('release.json is for another version');
                    }
                    Workdir::saveManifest($body, $signature);
                }
                // a source that goes back to an older signed release (one with a known fault, say) is not followed
                $seen = State::get('moduleUpdateSeen');
                if(!empty($seen['version']) && version_compare($stored['version'], (string)$seen['version'], '<'))
                {
                    throw new \RuntimeException('offers ' . $stored['version'] . ', older than the release ' . $seen['version'] . ' seen before');
                }
                if(empty($seen['version']) || version_compare($stored['version'], (string)$seen['version'], '>'))
                {
                    State::set(['version' => $stored['version'], 'released' => (string)$stored['released']], 'moduleUpdateSeen');
                }

                return $found + ['verified' => true, 'notes' => mb_substr($stored['notes'], 0, 20000), 'released' => $stored['released'],
                                 'min_php' => $stored['min_php'], 'min_whmcs' => $stored['min_whmcs'], 'manual_only' => $stored['manual_only'],
                                 'rollback_safe' => $stored['rollback_safe'], 'size' => $stored['edition']['size'],
                                 'zipUrl' => $info['assetBase'] . rawurlencode($stored['edition']['asset'])];
            }
            catch(\Throwable $e)
            {
                $errors[] = $source['name'] . ': ' . $e->getMessage();
            }
        }

        throw new \RuntimeException($errors ? implode('; ', $errors) : 'No release source');
    }

    protected static function sources()
    {
        if(defined('TSSGGS_UPDATE_SOURCE'))
        {
            return [['type' => 'cdn', 'name' => 'test source', 'base' => rtrim((string)constant('TSSGGS_UPDATE_SOURCE'), '/') . '/']];
        }

        return [
            ['type' => 'github', 'name' => 'GitHub', 'repo' => (string)Edition::value('releaseRepo')],
            ['type' => 'cdn', 'name' => 'CDN', 'base' => 'https://ggssl.com/cdn/releases/' . Edition::value('cdnDir') . '/'],
        ];
    }

    protected static function githubLatest(array $source, array $state)
    {
        $headers = ['Accept: application/vnd.github+json', 'X-GitHub-Api-Version: 2022-11-28'];
        // conditional request: an unchanged answer does not count against GitHub's limit for this IP
        if(!empty($state['etag']) && ($state['source'] ?? '') === 'GitHub' && !empty($state['version']))
        {
            $headers[] = 'If-None-Match: ' . $state['etag'];
        }
        list($status, $body, $response) = Http::get('https://api.github.com/repos/' . $source['repo'] . '/releases/latest', $headers, 1048576);
        if($status === 304)
        {
            return ['version' => $state['version'], 'page' => $state['page'] ?? '', 'etag' => $state['etag'],
                    'manifestUrl' => $state['manifestUrl'] ?? '', 'signatureUrl' => $state['signatureUrl'] ?? '', 'assetBase' => $state['assetBase'] ?? ''];
        }
        if($status !== 200)
        {
            throw new \RuntimeException('HTTP ' . $status);
        }
        $release = json_decode($body, true);
        $version = ltrim((string)($release['tag_name'] ?? ''), 'v');
        if(!preg_match('/^\d+\.\d+\.\d+$/', $version))
        {
            throw new \RuntimeException('Unexpected release tag');
        }
        $assets = [];
        foreach((array)($release['assets'] ?? []) as $asset)
        {
            $assets[(string)($asset['name'] ?? '')] = (string)($asset['browser_download_url'] ?? '');
        }
        $base = 'https://github.com/' . $source['repo'] . '/releases/download/v' . $version . '/';

        return ['version' => $version, 'page' => 'https://github.com/' . $source['repo'] . '/releases/tag/v' . $version, 'etag' => $response['etag'] ?? '',
                'manifestUrl' => $assets['release.json'] ?? ($base . 'release.json'), 'signatureUrl' => $assets['release.json.sig'] ?? ($base . 'release.json.sig'),
                'assetBase' => $base];
    }

    protected static function cdnLatest(array $source)
    {
        // the CDN copy has no separate "latest" record: the version is read from release.json itself (and believed
        // only once its signature checks out - unverified, it is shown as a link at most)
        list($status, $body) = Http::get($source['base'] . 'release.json', [], self::MANIFEST_MAX);
        if($status !== 200)
        {
            throw new \RuntimeException('HTTP ' . $status);
        }
        $data    = json_decode($body, true);
        $version = (string)($data['version'] ?? '');
        if(!preg_match('/^\d+\.\d+\.\d+$/', $version))
        {
            throw new \RuntimeException('Unexpected release.json');
        }

        return ['version' => $version, 'page' => 'https://github.com/' . Edition::value('releaseRepo') . '/releases',
                'manifestUrl' => $source['base'] . 'release.json', 'signatureUrl' => $source['base'] . 'release.json.sig', 'assetBase' => $source['base']];
    }

    /**
     * Downloads release.json and its signature (verified by the caller)
     * @return array [body, signature]
     */
    public static function fetchManifest($manifestUrl, $signatureUrl)
    {
        list($status, $body) = Http::get($manifestUrl, [], self::MANIFEST_MAX);
        if($status !== 200)
        {
            throw new \RuntimeException('release.json: HTTP ' . $status);
        }
        list($status, $signature) = Http::get($signatureUrl, [], 4096);
        if($status !== 200)
        {
            throw new \RuntimeException('release.json.sig: HTTP ' . $status);
        }

        return [$body, $signature];
    }

    /**
     * The manifest of this edition, after the signature and every field it relies on checked out
     */
    public static function verify($body, $signatureFile)
    {
        $signature = json_decode((string)$signatureFile, true);
        $keys      = self::keys();
        $keyId     = (string)($signature['key'] ?? '');
        $raw       = base64_decode((string)($signature['sig'] ?? ''), true);
        if(!isset($keys[$keyId]) || $raw === false || strlen($raw) !== 64)
        {
            throw new \RuntimeException('Release signed with an unknown or revoked key');
        }
        if(!sodium_crypto_sign_verify_detached($raw, (string)$body, $keys[$keyId]))
        {
            throw new \RuntimeException('Release signature does not match');
        }

        $data = json_decode((string)$body, true);
        if(!is_array($data) || (int)($data['format'] ?? 0) !== 1 || !preg_match('/^\d+\.\d+\.\d+$/', (string)($data['version'] ?? '')))
        {
            throw new \RuntimeException('Unsupported release.json');
        }
        // a key revoked by a signed release stays revoked
        if(!empty($data['revoked_keys']))
        {
            $revoked = array_values(array_unique(array_merge((array)(State::get()['revokedKeys'] ?? []), array_map('strval', (array)$data['revoked_keys']))));
            State::merge(['revokedKeys' => $revoked]);
            if(in_array($keyId, $revoked, true))
            {
                throw new \RuntimeException('Release signed with a revoked key');
            }
        }

        $edition = $data['editions'][Edition::get()] ?? null;
        if(!is_array($edition) || ($edition['folder'] ?? '') !== Edition::folder() || !preg_match('/^[A-Za-z0-9._-]+\.zip$/', (string)($edition['asset'] ?? ''))
            || !preg_match('/^[0-9a-f]{64}$/', (string)($edition['sha256'] ?? '')) || (int)($edition['size'] ?? 0) <= 0 || (int)$edition['size'] > self::ZIP_MAX
            || empty($edition['files']) || !is_array($edition['files']))
        {
            throw new \RuntimeException('release.json has no valid entry for this module');
        }
        foreach($edition['files'] as $path => $hash)
        {
            if(!self::safePath((string)$path) || !preg_match('/^[0-9a-f]{64}$/', (string)$hash))
            {
                throw new \RuntimeException('release.json lists an unsafe file: ' . $path);
            }
        }

        return [
            'version'       => (string)$data['version'],
            'released'      => (string)($data['released'] ?? ''),
            'notes'         => (string)($data['notes'] ?? ''),
            'min_php'       => (string)($data['min_php'] ?? ''),
            'min_whmcs'     => (string)($data['min_whmcs'] ?? ''),
            'rollback_safe' => !isset($data['rollback_safe']) || (bool)$data['rollback_safe'],
            'manual_only'   => !empty($data['manual_only']),
            'key'           => $keyId,
            'edition'       => ['asset' => (string)$edition['asset'], 'size' => (int)$edition['size'], 'sha256' => (string)$edition['sha256'], 'files' => $edition['files']],
        ];
    }

    /** A path inside the package: modules/addons/<folder>/... or modules/servers/<folder>/..., nothing else */
    public static function safePath($path)
    {
        $folder = preg_quote(Edition::folder(), '#');

        return (bool)preg_match('#^modules/(addons|servers)/' . $folder . '/[A-Za-z0-9_\-./ @+~]+$#', $path)
            && strpos($path, '..') === false && strpos($path, '//') === false && strpos($path, "\0") === false
            && !preg_match('#(^|/)\.\.?(/|$)#', $path);
    }
}
