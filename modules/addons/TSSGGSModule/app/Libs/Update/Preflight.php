<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Update;

/**
 * Whether this server can take a one-click update. Every check that fails turns the update into "notify only": the
 * admin sees the reason and the download link, nothing is touched.
 *
 * @return array list of ['key', 'ok' (bool), 'detail' (text), 'blocking' (bool)]
 */
class Preflight
{
    public static function run(array $state = null, $withLoopback = true)
    {
        $state  = $state ?? State::get();
        $checks = [];
        $add    = function($key, $ok, $detail = '', $blocking = true) use (&$checks) {
            $checks[] = ['key' => $key, 'ok' => (bool)$ok, 'detail' => (string)$detail, 'blocking' => $blocking];
        };

        $add('signing', Release::canVerify(), !function_exists('sodium_crypto_sign_verify_detached')
            ? 'PHP sodium extension missing: releases can not be verified' : (Release::keys() ? '' : 'no release signing key in this version'));
        $add('verified', !empty($state['verified']), empty($state['verified']) ? 'the release could not be verified' : '');
        $add('manual', empty($state['manual_only']), !empty($state['manual_only']) ? 'this release must be installed by hand (see its notes)' : '');

        $minPhp = (string)($state['min_php'] ?? '');
        $add('php', $minPhp === '' || version_compare(PHP_VERSION, $minPhp, '>='), 'PHP ' . PHP_VERSION . ($minPhp !== '' ? ', needs ' . $minPhp : ''));
        $whmcs    = (string)\WHMCS\Config\Setting::getValue('Version');
        $minWhmcs = (string)($state['min_whmcs'] ?? '');
        $add('whmcs', $minWhmcs === '' || version_compare(preg_replace('/-.*$/', '', $whmcs), $minWhmcs, '>='), 'WHMCS ' . $whmcs . ($minWhmcs !== '' ? ', needs ' . $minWhmcs : ''));

        $extensions = class_exists('ZipArchive') && function_exists('curl_init') && function_exists('hash_file');
        $add('extensions', $extensions, $extensions ? '' : 'needs the PHP zip and curl extensions');
        $add('os', PHP_OS_FAMILY !== 'Windows', PHP_OS_FAMILY === 'Windows' ? 'Windows servers can not swap folders that are in use' : '');

        // write access: the two module folders are renamed (their parents must be writable) and later deleted
        // (everything inside them must be writable); the updater's folder is created next to them
        $problems = [];
        foreach([Workdir::modulesDir(), dirname(Workdir::addonDir()), dirname(Workdir::serverDir())] as $dir)
        {
            if(!is_writable($dir))
            {
                $problems[] = $dir;
            }
        }
        foreach([Workdir::addonDir(), Workdir::serverDir()] as $dir)
        {
            $locked = self::firstNotWritable($dir);
            if($locked !== null)
            {
                $problems[] = $locked;
            }
        }
        $add('writable', !$problems, $problems ? 'not writable by PHP: ' . implode(', ', array_slice($problems, 0, 3)) : 'owner ' . self::owner(Workdir::addonDir()));

        // a rename is instant only within one disk
        $same    = @stat(Workdir::modulesDir())['dev'] ?? null;
        $oneDisk = $same !== null && $same === (@stat(dirname(Workdir::addonDir()))['dev'] ?? null) && $same === (@stat(dirname(Workdir::serverDir()))['dev'] ?? null);
        $add('disk', $oneDisk, $oneDisk ? '' : 'the module folders are on different disks');

        $need = 3 * max((int)($state['size'] ?? 0) * 4, 40 * 1048576); // the ZIP unpacks to about 4x its size
        $free = @disk_free_space(Workdir::modulesDir());
        $add('space', $free === false || $free >= $need, $free === false ? '' : round($free / 1048576) . ' MB free, needs ' . round($need / 1048576) . ' MB');

        $journal = Swap::journal();
        $idle    = !$journal || in_array($journal['state'] ?? '', ['committed', 'rolled_back'], true);
        $add('idle', $idle, $idle ? '' : 'an update is already in progress');

        $opcache = self::opcacheOk();
        if($withLoopback)
        {
            $health = Health::ping();
            $add('loopback', $health === true, $health === true ? '' : 'the server can not reach its own WHMCS address (' . $health . '): the new version could not be checked before it is kept');
            // what counts is the web server's OPcache, which a command-line run does not see
            if($health === true && isset(Health::$lastAnswer['opcache_ok']))
            {
                $opcache = (bool)Health::$lastAnswer['opcache_ok'];
            }
        }
        $add('opcache', $opcache, $opcache ? '' : 'OPcache never rechecks files and the module may not clear it: ask the host to restart PHP after a manual update');

        // local changes: warned about, the admin decides
        $changed = self::changedFiles();
        $add('changes', !$changed, $changed ? count($changed) . ' file(s) changed by hand, e.g. ' . implode(', ', array_slice($changed, 0, 3)) . ' - they will be replaced' : '', false);

        return $checks;
    }

    public static function passed(array $checks)
    {
        foreach($checks as $check)
        {
            if(!$check['ok'] && $check['blocking'])
            {
                return false;
            }
        }

        return true;
    }

    /** storage/ is moved as one folder and never rewritten: what lies inside it does not matter */
    protected static function firstNotWritable($dir)
    {
        if(!is_writable($dir))
        {
            return $dir;
        }
        $storage  = $dir . DIRECTORY_SEPARATOR . 'storage';
        $filter   = new \RecursiveCallbackFilterIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS), function($item) use ($storage) {
            return $item->getPathname() !== $storage;
        });
        $iterator = new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::SELF_FIRST);
        foreach($iterator as $item)
        {
            if($item->isDir() && !$item->isWritable())
            {
                return $item->getPathname();
            }
        }

        return null;
    }

    protected static function owner($path)
    {
        $uid = @fileowner($path);
        if($uid === false)
        {
            return '?';
        }
        if(function_exists('posix_getpwuid'))
        {
            $info = @posix_getpwuid($uid);
            if($info)
            {
                return $info['name'];
            }
        }

        return (string)$uid;
    }

    /** OPcache that never rechecks files is fine only when the module may clear it */
    public static function opcacheOk()
    {
        if(!function_exists('opcache_get_status') || !ini_get('opcache.enable'))
        {
            return true;
        }
        if(ini_get('opcache.validate_timestamps'))
        {
            return true;
        }

        return function_exists('opcache_reset') && !self::disabled('opcache_reset') && (string)ini_get('opcache.restrict_api') === '';
    }

    protected static function disabled($function)
    {
        return in_array($function, array_map('trim', explode(',', (string)ini_get('disable_functions'))), true);
    }

    /**
     * Files of the installed version that differ from what it shipped (app/Config/release-files.json, written by the
     * build). storage/ and the module's own state are left out.
     */
    public static function changedFiles()
    {
        $list = json_decode((string)@file_get_contents(Workdir::addonDir() . '/app/Config/release-files.json'), true);
        if(!is_array($list))
        {
            return [];
        }
        $changed = [];
        $root    = dirname(Workdir::modulesDir());
        foreach($list as $path => $hash)
        {
            $file = $root . '/' . $path;
            if(!is_file($file) || hash_file('sha256', $file) !== $hash)
            {
                $changed[] = $path;
                if(count($changed) >= 50)
                {
                    break;
                }
            }
        }

        return $changed;
    }
}
