<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Update;

use ModulesGarden\TSSGGSModule\App\Libs\Edition;

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
        file_put_contents(self::path('release.json'), $body);
        file_put_contents(self::path('release.json.sig'), $signature);
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
