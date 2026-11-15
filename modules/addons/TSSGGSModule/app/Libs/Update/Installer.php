<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Update;

use ModulesGarden\TSSGGSModule\App\Libs\Edition;
use ModulesGarden\TSSGGSModule\App\Libs\GeneralSettings;
use WHMCS\Database\Capsule;

/**
 * One-click update, in three steps the admin page calls one after another (each its own request, so none runs long):
 *   1 prepare  download the ZIP, check size, SHA-256 and the signed manifest, unpack only the files it lists into
 *              new/, each checked against its hash          - nothing of the live module touched yet
 *   2 backup   ZIP of the running version (without storage/) and of the module's settings, the last 3 kept
 *   3 install  pause the module's crons, swap the folders, let the new version upgrade itself and answer in a
 *              separate request (App\Libs\Update\Health), keep it - or put the previous version back
 * The command line runs all three: php cron/cron.php ModuleUpdate --install
 */
class Installer
{
    const MAX_FILES     = 20000;
    const MAX_UNPACKED  = 314572800; // 300 MB
    const KEEP_BACKUPS  = 3;

    /** Everything a step needs, checked again at every step */
    protected static function release($withLoopback = false)
    {
        $state = State::get();
        if(!Release::available($state))
        {
            throw new \RuntimeException('No newer release is available');
        }
        if(!Preflight::passed(Preflight::run($state, $withLoopback)))
        {
            throw new \RuntimeException('This server can not take the update automatically (see the checks on the update page)');
        }
        $manifest = Workdir::manifest();
        if(!$manifest || $manifest['version'] !== $state['version'])
        {
            throw new \RuntimeException('The release could not be verified');
        }

        return [$state, $manifest];
    }

    /** Step 1 */
    public static function prepare()
    {
        @set_time_limit(0);
        list($state, $manifest) = self::release();
        $edition = $manifest['edition'];

        Workdir::ensure('download');
        $zip  = Workdir::path('download', $edition['asset']);
        $part = $zip . '.part';
        if(!is_file($zip) || filesize($zip) !== $edition['size'] || hash_file('sha256', $zip) !== $edition['sha256'])
        {
            @unlink($zip);
            try
            {
                list($status) = Http::download((string)$state['zipUrl'], $part, $edition['size']);
                if($status !== 200)
                {
                    throw new \RuntimeException('Download: HTTP ' . $status);
                }
                clearstatcache(true, $part);
                if(filesize($part) !== $edition['size'])
                {
                    throw new \RuntimeException('Download incomplete (' . filesize($part) . ' of ' . $edition['size'] . ' bytes)');
                }
                if(hash_file('sha256', $part) !== $edition['sha256'])
                {
                    throw new \RuntimeException('Downloaded file does not match the signed release');
                }
                rename($part, $zip);
            }
            finally
            {
                @unlink($part);
            }
        }

        self::unpack($zip, $edition['files']);
        file_put_contents(Workdir::path('new', 'version.txt'), $manifest['version']);

        return ['version' => $manifest['version'], 'files' => count($edition['files'])];
    }

    /**
     * Unpacks into new/ exactly the files the manifest lists (anything else in the ZIP stops the update), each
     * checked against its hash. Paths are mapped by name, never taken as given.
     */
    protected static function unpack($zipFile, array $files)
    {
        $staging = Workdir::path('new');
        Workdir::removeTree($staging);
        Workdir::ensure('new');

        $zip = new \ZipArchive();
        if($zip->open($zipFile) !== true)
        {
            throw new \RuntimeException('The release ZIP can not be opened');
        }
        try
        {
            if($zip->numFiles > self::MAX_FILES)
            {
                throw new \RuntimeException('The release ZIP holds too many files');
            }
            $total = 0;
            $seen  = [];
            for($i = 0; $i < $zip->numFiles; $i++)
            {
                $stat = $zip->statIndex($i);
                $name = str_replace('\\', '/', (string)$stat['name']);
                if(substr($name, -1) === '/')
                {
                    continue; // folders are made from the file paths
                }
                if(!isset($files[$name]) || !Release::safePath($name))
                {
                    throw new \RuntimeException('Unexpected file in the release ZIP: ' . $name);
                }
                $total += (int)$stat['size'];
                if($total > self::MAX_UNPACKED)
                {
                    throw new \RuntimeException('The release ZIP unpacks to more than expected');
                }
                $target = $staging . '/' . substr($name, strlen('modules/'));
                if(!is_dir(dirname($target)) && !@mkdir(dirname($target), 0755, true) && !is_dir(dirname($target)))
                {
                    throw new \RuntimeException('Cannot create ' . dirname($target));
                }
                $in  = $zip->getStream($stat['name']);
                $out = fopen($target, 'wb');
                if(!$in || !$out)
                {
                    throw new \RuntimeException('Cannot unpack ' . $name);
                }
                stream_copy_to_stream($in, $out, self::MAX_UNPACKED);
                fclose($in);
                fclose($out);
                @chmod($target, 0644);
                if(hash_file('sha256', $target) !== $files[$name])
                {
                    throw new \RuntimeException('File does not match the signed release: ' . $name);
                }
                $seen[$name] = true;
            }
            $missing = array_diff_key($files, $seen);
            if($missing)
            {
                throw new \RuntimeException('The release ZIP misses ' . count($missing) . ' file(s), e.g. ' . key($missing));
            }
        }
        catch(\Throwable $e)
        {
            $zip->close();
            Workdir::removeTree($staging);
            throw $e;
        }
        $zip->close();

        // storage/ is the install's own (logs, generated files): the running one is moved across during the swap;
        // what the new version adds to it is copied in now
        $newStorage = $staging . '/addons/' . Edition::folder() . '/storage';
        if(is_dir($newStorage))
        {
            self::copyMissing($newStorage, Workdir::addonDir() . '/storage');
            Workdir::removeTree($newStorage);
        }
    }

    protected static function copyMissing($from, $to)
    {
        if(!is_dir($to))
        {
            @mkdir($to, 0755, true);
        }
        foreach(scandir($from) as $entry)
        {
            if($entry === '.' || $entry === '..')
            {
                continue;
            }
            $source = $from . '/' . $entry;
            $target = $to . '/' . $entry;
            if(is_dir($source))
            {
                self::copyMissing($source, $target);
            }
            elseif(!file_exists($target))
            {
                @copy($source, $target);
            }
        }
    }

    /** Step 2 */
    public static function backup()
    {
        @set_time_limit(0);
        self::release();
        $version = State::installedVersion();
        $file    = Workdir::ensure('backups') . '/' . Edition::folder() . '-' . $version . '-' . date('Ymd-His') . '.zip';

        $zip = new \ZipArchive();
        if($zip->open($file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true)
        {
            throw new \RuntimeException('Cannot create the backup');
        }
        $root = dirname(Workdir::modulesDir());
        foreach([Workdir::addonDir(), Workdir::serverDir()] as $dir)
        {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
            foreach($iterator as $item)
            {
                $path = str_replace('\\', '/', substr($item->getPathname(), strlen($root) + 1));
                if(strpos($path, 'modules/addons/' . Edition::folder() . '/storage/') === 0 || !$item->isFile())
                {
                    continue;
                }
                $zip->addFile($item->getPathname(), $path);
            }
        }
        // settings, for a manual recovery only: nothing restores them automatically
        $settings = Capsule::table('tbladdonmodules')->whereIn('module', [Edition::folder(), 'configuration_' . Edition::folder()])->get(['module', 'setting', 'value']);
        $zip->addFromString('settings.json', json_encode($settings));
        $zip->setArchiveComment(json_encode(['version' => $version, 'created' => date('c')]));
        if(!$zip->close())
        {
            @unlink($file);
            throw new \RuntimeException('Cannot write the backup');
        }

        // the last few are kept
        $backups = glob(Workdir::path('backups') . '/' . Edition::folder() . '-*.zip') ?: [];
        usort($backups, function($a, $b) { return filemtime($b) <=> filemtime($a); });
        foreach(array_slice($backups, self::KEEP_BACKUPS) as $old)
        {
            @unlink($old);
        }

        return ['backup' => basename($file)];
    }

    /** Step 3 */
    public static function install($adminId = 0)
    {
        @set_time_limit(0);
        @ignore_user_abort(true);
        list($state, $manifest) = self::release(true);
        if(!is_dir(Workdir::path('new', 'addons', Edition::folder())) || trim((string)@file_get_contents(Workdir::path('new', 'version.txt'))) !== $manifest['version'])
        {
            throw new \RuntimeException('Run the download step first');
        }
        if(!self::latestBackup(State::installedVersion()))
        {
            throw new \RuntimeException('Run the backup step first');
        }

        return self::swapTo($manifest['version'], $adminId, 'update', $manifest['rollback_safe']);
    }

    /**
     * The swap shared by update and rollback: new/ must hold the target version
     */
    protected static function swapTo($toVersion, $adminId, $kind, $rollbackSafe)
    {
        $from = State::installedVersion();
        // every class used after the swap is loaded now: the autoloader would otherwise read the NEW files into
        // this process, which still runs the old ones
        foreach([Health::class, State::class, Workdir::class, Edition::class, GeneralSettings::class] as $class)
        {
            class_exists($class, true);
        }

        Swap::lock();
        $crons = null;
        try
        {
            Swap::writeRecoveryScript();
            $crons = Swap::pauseCrons();
            self::log(ucfirst($kind) . ' ' . $from . ' -> ' . $toVersion . ' started. If the site breaks, run: php ' . Workdir::path('recover.php'), $adminId);

            Swap::forward($from, $toVersion);
            $health = Health::upgradeAndCheck($toVersion, $from);
            if(empty($health['ok']))
            {
                Swap::rollBack($from, $toVersion, (string)$health['error']);
                // the previous files are back: they write their own version again
                Capsule::table('tbladdonmodules')->where('module', Edition::folder())->where('setting', 'version')->update(['value' => $from]);
                $message = ucfirst($kind) . ' to ' . $toVersion . ' failed and was undone: ' . $health['error'];
                self::log($message, $adminId);
                State::history(['kind' => $kind, 'from' => $from, 'to' => $toVersion, 'result' => 'rolled back', 'error' => $health['error'], 'admin' => $adminId]);
                throw new \RuntimeException($message);
            }
            Swap::commit($from, $toVersion);
        }
        finally
        {
            Swap::resumeCrons($crons);
            Swap::unlock();
        }

        Workdir::removeTree(Workdir::path('download'));
        State::merge(['rollbackSafe' => (bool)$rollbackSafe, 'installedAt' => time()]);
        State::history(['kind' => $kind, 'from' => $from, 'to' => $toVersion, 'result' => 'ok', 'admin' => $adminId]);
        $message = ucfirst($kind) . ' ' . $from . ' -> ' . $toVersion . ' completed';
        self::log($message, $adminId);
        localAPI('SendAdminEmail', ['customsubject' => Edition::name() . ': ' . $message, 'type' => 'system',
            'custommessage' => '<p>' . htmlspecialchars(Edition::name() . ' was updated from ' . $from . ' to ' . $toVersion . '.') . '</p>']);

        return ['from' => $from, 'to' => $toVersion];
    }

    /** The newest backup of a version, if any */
    public static function latestBackup($version = null)
    {
        $pattern = Workdir::path('backups') . '/' . Edition::folder() . '-' . ($version !== null ? $version . '-' : '') . '*.zip';
        $files   = glob($pattern) ?: [];
        usort($files, function($a, $b) { return filemtime($b) <=> filemtime($a); });

        return $files[0] ?? null;
    }

    /** The version a backup ZIP holds */
    public static function backupVersion($file)
    {
        $zip = new \ZipArchive();
        if($zip->open($file) !== true)
        {
            return null;
        }
        $comment = json_decode((string)$zip->getArchiveComment(), true);
        $zip->close();

        return is_array($comment) ? ($comment['version'] ?? null) : null;
    }

    /**
     * The previous version back from its backup. Offered only when the installed release says its database changes
     * are safe for older code (release.json "rollback_safe").
     */
    public static function rollback($adminId = 0)
    {
        @set_time_limit(0);
        @ignore_user_abort(true);
        $state = State::get();
        if(isset($state['rollbackSafe']) && !$state['rollbackSafe'])
        {
            throw new \RuntimeException('This version can not be rolled back automatically (its release notes explain why)');
        }
        $current = State::installedVersion();
        $backup  = null;
        foreach(glob(Workdir::path('backups') . '/' . Edition::folder() . '-*.zip') ?: [] as $file)
        {
            $version = self::backupVersion($file);
            if($version && version_compare($version, $current, '<') && (!$backup || filemtime($file) > filemtime($backup)))
            {
                $backup = $file;
            }
        }
        if(!$backup)
        {
            throw new \RuntimeException('No backup of an earlier version');
        }
        $target = self::backupVersion($backup);

        // the backup is unpacked like a release, without the hash list: it is this install's own file
        $staging = Workdir::path('new');
        Workdir::removeTree($staging);
        Workdir::ensure('new');
        $zip = new \ZipArchive();
        if($zip->open($backup) !== true)
        {
            throw new \RuntimeException('The backup can not be opened');
        }
        for($i = 0; $i < $zip->numFiles; $i++)
        {
            $name = str_replace('\\', '/', (string)$zip->getNameIndex($i));
            if($name === 'settings.json' || substr($name, -1) === '/')
            {
                continue;
            }
            if(!Release::safePath($name))
            {
                $zip->close();
                throw new \RuntimeException('Unexpected file in the backup: ' . $name);
            }
            $file = $staging . '/' . substr($name, strlen('modules/'));
            @mkdir(dirname($file), 0755, true);
            file_put_contents($file, $zip->getFromIndex($i));
        }
        $zip->close();

        return self::swapTo($target, $adminId, 'rollback', true);
    }

    protected static function log($message, $adminId)
    {
        if(function_exists('logActivity'))
        {
            \logActivity(Edition::name() . ': ' . $message, (int)$adminId);
        }
    }
}
