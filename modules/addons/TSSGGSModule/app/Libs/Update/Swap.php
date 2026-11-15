<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Update;

/**
 * Replaces the two module folders (modules/addons/<folder>, modules/servers/<folder>) by renames, never by copying,
 * so no request ever sees half-old, half-new files of one folder.
 *
 * For each folder:  live -> old/,  new/ -> live  (the addon's storage/ is moved from old into the new folder).
 * A crash between two renames leaves a state the filesystem itself describes (which of live, old/, new/ exist), so
 * repair needs no record of how far it got: recover() always goes back to the previous version unless the journal
 * says the update was committed. The same logic is written to <workdir>/recover.php, which runs without the module
 * (`php recover.php`) and is also started by the module's hooks.php files when they find an abandoned swap.
 */
class Swap
{
    /** @var resource|null held for the whole swap: a crashed process releases it, which is how recovery knows */
    protected static $lock;

    public static function journal()
    {
        $data = json_decode((string)@file_get_contents(Workdir::path('journal.json')), true);

        return is_array($data) ? $data : null;
    }

    protected static function writeJournal(array $data)
    {
        $data['updated'] = time();
        $file            = Workdir::path('journal.json');
        file_put_contents($file . '.tmp', json_encode($data));
        rename($file . '.tmp', $file);
    }

    /** The folder triples [live, old, new] of the addon and the server module */
    public static function components()
    {
        $folder = basename(Workdir::addonDir());

        return [
            'addons'  => [Workdir::addonDir(), Workdir::path('old', 'addons', $folder), Workdir::path('new', 'addons', $folder)],
            'servers' => [Workdir::serverDir(), Workdir::path('old', 'servers', $folder), Workdir::path('new', 'servers', $folder)],
        ];
    }

    public static function lock()
    {
        Workdir::ensure();
        self::$lock = self::openLock('update.lock');
        if(!self::$lock)
        {
            throw new \RuntimeException('Cannot open ' . Workdir::path('update.lock'));
        }
        if(!flock(self::$lock, LOCK_EX | LOCK_NB))
        {
            self::$lock = null;
            throw new \RuntimeException('Another update is running');
        }
    }

    /**
     * A lock file of the work folder. flock() needs no write access, so a file another user (web server / cron)
     * created is opened read-only when it can not be opened for writing.
     */
    public static function openLock($name)
    {
        $file   = Workdir::path($name);
        $exists = is_file($file);
        $handle = @fopen($file, 'c') ?: @fopen($file, 'r');
        if($handle && !$exists)
        {
            @chmod($file, 0664);
        }

        return $handle ?: null;
    }

    public static function unlock()
    {
        if(self::$lock)
        {
            flock(self::$lock, LOCK_UN);
            fclose(self::$lock);
            self::$lock = null;
        }
    }

    /**
     * Waits until no cron of the module runs (they hold cron.lock shared, see cron/cron.php); new ones do not start
     * while State "moduleUpdateLock" is set
     */
    public static function pauseCrons($timeout = 300)
    {
        State::set(['since' => time()], 'moduleUpdateLock');
        $handle = self::openLock('cron.lock');
        if(!$handle)
        {
            State::forget('moduleUpdateLock');
            throw new \RuntimeException('Cannot open ' . Workdir::path('cron.lock'));
        }
        $until  = time() + $timeout;
        while(!flock($handle, LOCK_EX | LOCK_NB))
        {
            if(time() > $until)
            {
                fclose($handle);
                State::forget('moduleUpdateLock');
                throw new \RuntimeException('A module cron job is still running after ' . $timeout . ' seconds; try again later');
            }
            sleep(1);
        }

        return $handle;
    }

    public static function resumeCrons($handle)
    {
        if($handle)
        {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
        State::forget('moduleUpdateLock');
    }

    /**
     * new/ -> live. Throws on the first failed rename after putting back what was moved.
     */
    public static function forward($from, $to)
    {
        $components = self::components();
        foreach($components as $name => list($live, $old, $new))
        {
            if(!is_dir($new))
            {
                throw new \RuntimeException('Staged ' . $name . ' folder missing');
            }
            Workdir::ensure('old', $name);
            if(is_dir($old))
            {
                Workdir::removeTree($old);
            }
        }

        self::writeJournal(['state' => 'swapping', 'from' => $from, 'to' => $to, 'pid' => getmypid()]);
        try
        {
            foreach($components as $name => list($live, $old, $new))
            {
                self::rename($live, $old);
                self::rename($new, $live);
                if($name === 'addons' && is_dir($old . '/storage'))
                {
                    if(is_dir($live . '/storage'))
                    {
                        Workdir::removeTree($live . '/storage');
                    }
                    self::rename($old . '/storage', $live . '/storage');
                }
            }
        }
        catch(\Throwable $e)
        {
            self::backward();
            self::writeJournal(['state' => 'rolled_back', 'from' => $from, 'to' => $to, 'error' => $e->getMessage()]);
            throw $e;
        }
        self::writeJournal(['state' => 'swapped', 'from' => $from, 'to' => $to, 'pid' => getmypid()]);
        self::clearOpcache();
    }

    /** Back to the folders in old/ (the new ones return to new/) - whatever point the swap had reached */
    public static function backward()
    {
        // per folder: live missing + old there = stopped between the two renames; both there = swapped;
        // no old = never touched
        foreach(array_reverse(self::components(), true) as $name => list($live, $old, $new))
        {
            if(!is_dir($live) && is_dir($old))
            {
                @rename($old, $live);
                continue;
            }
            if(is_dir($live) && is_dir($old))
            {
                if($name === 'addons' && is_dir($live . '/storage') && !is_dir($old . '/storage'))
                {
                    @rename($live . '/storage', $old . '/storage');
                }
                if(is_dir($new))
                {
                    Workdir::removeTree($new);
                }
                @rename($live, $new);
                @rename($old, $live);
            }
        }
        self::clearOpcache();
    }

    public static function commit($from, $to)
    {
        self::writeJournal(['state' => 'committed', 'from' => $from, 'to' => $to]);
        Workdir::removeTree(Workdir::path('old'));
        Workdir::removeTree(Workdir::path('new'));
        @unlink(Workdir::path('journal.json'));
    }

    public static function rollBack($from, $to, $error)
    {
        self::backward();
        self::writeJournal(['state' => 'rolled_back', 'from' => $from, 'to' => $to, 'error' => $error]);
        Workdir::removeTree(Workdir::path('old'));
        Workdir::removeTree(Workdir::path('new'));
        @unlink(Workdir::path('journal.json'));
    }

    protected static function rename($from, $to)
    {
        if(!@rename($from, $to))
        {
            $error = error_get_last();
            throw new \RuntimeException('Cannot rename ' . $from . ' to ' . $to . ($error ? ': ' . $error['message'] : ''));
        }
    }

    public static function clearOpcache()
    {
        clearstatcache(true);
        if(function_exists('opcache_reset'))
        {
            @opcache_reset();
        }
    }

    /**
     * The stand-alone repair script, written before every swap. It knows the three paths of each folder and repeats
     * backward() without any module code.
     */
    public static function writeRecoveryScript()
    {
        $paths = [];
        foreach(self::components() as $name => $triple)
        {
            $paths[$name] = $triple;
        }
        $script = '<?php' . "\n"
            . "// Repairs an interrupted update of the module (written by App\\Libs\\Update\\Swap). Run: php " . Workdir::path('recover.php') . "\n"
            . "// The module's hooks.php files include it too when they find an abandoned swap.\n"
            . "if(PHP_SAPI !== 'cli' && !defined('WHMCS')) { http_response_code(404); exit; }\n"
            . '$work = ' . var_export(Workdir::path(), true) . ";\n"
            . '$components = ' . var_export($paths, true) . ";\n"
            . <<<'PHP'
$journalFile = $work . '/journal.json';
$journal = json_decode((string)@file_get_contents($journalFile), true);
if(!is_array($journal) || !in_array($journal['state'] ?? '', ['swapping', 'swapped'], true)) { if(PHP_SAPI === 'cli') echo "Nothing to repair.\n"; return; }
// not while an update is running: the running update holds update.lock (released by the system if it died)
$lock = @fopen($work . '/update.lock', 'c') ?: @fopen($work . '/update.lock', 'r');
if(!$lock) { if(PHP_SAPI === 'cli') echo "Cannot open " . $work . "/update.lock: run this as the owner of the module files.\n"; return; }
if(!flock($lock, LOCK_EX | LOCK_NB)) { if(PHP_SAPI === 'cli') echo "An update is running right now.\n"; return; }
if(PHP_SAPI !== 'cli' && time() - (int)($journal['updated'] ?? 0) < 300) { flock($lock, LOCK_UN); return; }
$rm = function($dir) use (&$rm) { if(is_link($dir) || is_file($dir)) { @unlink($dir); return; } if(!is_dir($dir)) return; foreach(scandir($dir) as $e) { if($e !== '.' && $e !== '..') $rm($dir . '/' . $e); } @rmdir($dir); };
foreach(array_reverse($components, true) as $name => list($live, $old, $new)) {
    if(!is_dir($live) && is_dir($old)) { @rename($old, $live); continue; }
    if(is_dir($live) && is_dir($old)) {
        if($name === 'addons' && is_dir($live . '/storage') && !is_dir($old . '/storage')) @rename($live . '/storage', $old . '/storage');
        if(is_dir($new)) $rm($new);
        @rename($live, $new);
        @rename($old, $live);
    }
}
// leftovers hold runnable PHP: removed once every live folder is back (never while one is still missing)
$back = true;
foreach($components as $name => list($live, $old, $new)) { if(!is_dir($live) || is_dir($old)) $back = false; }
if($back) { $rm($work . '/new'); $rm($work . '/old'); }
$journal['state'] = $back ? 'rolled_back' : 'swapping'; $journal['error'] = $back ? 'interrupted update repaired by recover.php' : 'repair incomplete: check the module folders'; $journal['updated'] = time();
@file_put_contents($journalFile, json_encode($journal));
if(function_exists('opcache_reset')) @opcache_reset();
flock($lock, LOCK_UN);
if(PHP_SAPI === 'cli') echo $back ? "Repaired: the previous version of the module is back in place.\n" : "Repair incomplete: the module folders could not all be moved back (permissions?). Run this as the owner of the module files.\n";
PHP;
        file_put_contents(Workdir::path('recover.php'), $script . "\n");
    }
}
