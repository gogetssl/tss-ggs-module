<?php

namespace TSSGGSModule\App\Libs\Update;

use TSSGGSModule\App\Libs\Support\Edition;

/**
 * The updater's own folder: modules/.<folder>-update/ - next to the module folders, so on the same disk (the swap is
 * a rename, never a copy) and not scanned by WHMCS (it only looks into modules/addons/* and modules/servers/*).
 *
 *   release.json, release.json.sig   the newest release found (verified again every time it is used)
 *   download/                        the ZIP while it downloads (*.part) and once verified
 *   new/                             the verified files of the new version, unpacked
 *   old/                             the replaced module folders, only for the seconds of a swap
 *   backups/                         ZIPs of the replaced versions (the last 3), for the rollback
 *   journal.json                     the swap in progress (App\Libs\Update\Swap, recover.php)
 *   update.lock, cron.lock           locks of the update and of the module's running crons
 *   recover.php                      repairs an interrupted swap without the module (php recover.php)
 */
class Workdir
{
    /** sub-folders that only hold files of the update in progress (safe to set aside and make again) */
    const TEMPORARY = ['download', 'new'];

    /**
     * Group-writable, like the folders: the web server and the cron (command line) may run as different users of one
     * group, and each must be able to replace what the other wrote
     */
    public static function share($path)
    {
        @chmod($path, is_dir($path) ? 0775 : 0664);
    }

    /**
     * Work folder entries this user can not write to and can not replace: the folder itself, the backups folder, and
     * stored files whose folder is not writable either (a file in a writable folder is replaced, see saveManifest).
     * The temporary folders are set aside by ensure().
     * @return string[]
     */
    public static function notWritable()
    {
        $locked = [];
        if(!is_dir(self::path()))
        {
            return $locked;
        }
        foreach(['', 'backups'] as $entry)
        {
            $path = $entry === '' ? self::path() : self::path($entry);
            if(is_dir($path) && !is_writable($path))
            {
                $locked[] = $path;
            }
        }

        return $locked;
    }

    public static function modulesDir()
    {
        return dirname(__DIR__, 5); // .../modules
    }

    public static function path(...$parts)
    {
        return self::modulesDir() . DIRECTORY_SEPARATOR . '.' . strtolower(Edition::folder()) . '-update' . ($parts ? DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts) : '');
    }

    public static function addonDir()
    {
        return self::modulesDir() . DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR . Edition::folder();
    }

    public static function serverDir()
    {
        return self::modulesDir() . DIRECTORY_SEPARATOR . 'servers' . DIRECTORY_SEPARATOR . Edition::folder();
    }

    /** Creates the folder, closed to web requests where the server reads .htaccess */
    public static function ensure(...$parts)
    {
        $base = self::path();
        if(!is_dir($base))
        {
            if(!@mkdir($base, 0775) && !is_dir($base))
            {
                throw new \RuntimeException('Cannot create ' . $base);
            }
            // the cron (command line) and the web server may run as different users of one group
            @chmod($base, 0775);
        }
        if(!is_file($base . '/.htaccess'))
        {
            @file_put_contents($base . '/.htaccess', "Require all denied\nDeny from all\n");
            @file_put_contents($base . '/index.html', '');
        }
        $dir = self::path(...$parts);
        // a temporary folder left by another user (the web server or the cron, made by an older version without group
        // write) can not be written into: it is set aside (a rename needs only the parent folder) and made again
        if($parts && in_array($parts[0], self::TEMPORARY, true) && is_dir(self::path($parts[0])) && !is_writable(self::path($parts[0])))
        {
            @rename(self::path($parts[0]), self::path($parts[0] . '.stale-' . date('YmdHis')));
            clearstatcache();
        }
        if($parts && !is_dir($dir))
        {
            // group-writable like the folder itself: the web server and the cron may run as different users
            $path = $base;
            foreach($parts as $part)
            {
                $path .= DIRECTORY_SEPARATOR . $part;
                if(!is_dir($path))
                {
                    if(!@mkdir($path, 0775) && !is_dir($path))
                    {
                        throw new \RuntimeException('Cannot create ' . $path);
                    }
                    @chmod($path, 0775);
                }
            }
        }

        return $dir;
    }

    public static function removeTree($dir)
    {
        if(is_link($dir) || is_file($dir))
        {
            return @unlink($dir);
        }
        if(!is_dir($dir))
        {
            return true;
        }
        foreach(scandir($dir) as $entry)
        {
            if($entry !== '.' && $entry !== '..')
            {
                self::removeTree($dir . DIRECTORY_SEPARATOR . $entry);
            }
        }

        return @rmdir($dir);
    }

    public static function saveManifest($body, $signature)
    {
        self::ensure();
        foreach(['release.json' => $body, 'release.json.sig' => $signature] as $name => $content)
        {
            $file = self::path($name);
            // written by another user without group write: replaced (needs only the folder) instead of rewritten
            if(is_file($file) && !is_writable($file))
            {
                @unlink($file);
            }
            file_put_contents($file, $content);
            self::share($file);
        }
    }

    /** The stored release, verified again (null when there is none or it no longer verifies) */
    public static function manifest()
    {
        $body = @file_get_contents(self::path('release.json'));
        $sig  = @file_get_contents(self::path('release.json.sig'));
        if($body === false || $sig === false || !Release::canVerify())
        {
            return null;
        }

        return Release::verify($body, $sig);
    }
}
