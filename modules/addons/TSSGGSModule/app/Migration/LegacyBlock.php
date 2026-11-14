<?php

namespace ModulesGarden\TSSGGSModule\App\Migration;

use ModulesGarden\TSSGGSModule\App\Libs\Edition;
use ModulesGarden\TSSGGSModule\App\Migration\Support\Schema;
use WHMCS\Database\Capsule;

/**
 * While a migration runs, the legacy module must not act on the services it moves (M-25).
 *
 * The services are written to the unified tables in batches but stay on the legacy module until the switch, so
 * anything the legacy module does to them in between (a client reissue, an admin "Renew", a status sync of its cron)
 * would be lost, or would leave an order half old and half new. Paused for those services until the run ends
 * (completed or rolled back) - and for as long as a run that stopped with an error waits for its rollback:
 * - client: actions of the service page and the SSL configuration pages; looking at the page stays possible
 * - admin: saving the service and the legacy tab; module commands are aborted through the PreModule* hooks
 * - the legacy module's own scripts (crons, AJAX end points) and its addon page
 * A paid order, renewal or upgrade is not lost: the command is held and run again once the run has ended.
 * Services the run left on the legacy module (skipped, failed) are not paused.
 */
class LegacyBlock
{
    /** job kind of a held module command */
    const KIND_HELD = 'held';

    /**
     * Module commands a payment triggers once - held and run again when the run has ended, through the WHMCS
     * functions of includes/modulefunctions.php (the API has no command for a renewal)
     */
    const REPLAYED = ['Create' => 'ServerCreateAccount', 'Renew' => 'ServerRenew', 'ChangePackage' => 'ServerChangePackage'];

    /** every module command WHMCS lets a hook abort (suspension and termination are retried by WHMCS itself) */
    const COMMANDS = ['Create', 'Renew', 'ChangePackage', 'Suspend', 'Unsuspend', 'Terminate', 'ChangePassword', 'Custom'];

    /** query keys of a plain look at the client's service page */
    const VIEW_KEYS = ['action', 'id', 'language', 'currency', 'systpl', 'carttpl'];

    /** @var array|null per request: ['run' => ?array, 'server' => string, 'addon' => string] */
    protected static $state;

    public static function reset(): void
    {
        self::$state = null;
    }

    /**
     * Guard + hooks, from the addon's hooks.php. Never breaks a page: any error leaves the request alone.
     */
    public static function register(): void
    {
        try
        {
            self::guardRequest();
        }
        catch(\Throwable $e)
        {
            // a broken check must not take the store down
        }

        foreach(self::COMMANDS as $command)
        {
            add_hook('PreModule' . $command, 1, function($vars) use ($command) {
                try
                {
                    return LegacyBlock::onModuleCommand($command, (array)$vars);
                }
                catch(\Throwable $e)
                {
                    return [];
                }
            });
        }

        add_hook('ClientAreaProductDetailsOutput', 1, function($vars) {
            try
            {
                $serviceId = (int)(isset($vars['service']) && is_object($vars['service']) ? $vars['service']->id : ($vars['serviceid'] ?? 0));

                return LegacyBlock::blocks($serviceId) ? '<div class="alert alert-warning">' . htmlspecialchars(self::text('client')) . '</div>' : '';
            }
            catch(\Throwable $e)
            {
                return '';
            }
        });

        add_hook('AdminClientServicesTabFields', 1, function($vars) {
            try
            {
                $serviceId = (int)($vars['id'] ?? 0);
                if(!LegacyBlock::blocks($serviceId))
                {
                    return [];
                }
                $run  = self::run();
                $held = Capsule::table(Store::jobsTable())->where('migration_id', $run['id'])->where('kind', self::KIND_HELD)
                    ->where('object_id', $serviceId)->where('status', Store::JOB_PENDING)->pluck('reason')->all();
                $html = '<div class="alert alert-warning" style="margin:0">' . htmlspecialchars(self::adminText()) . '</div>';
                if($held)
                {
                    $html .= '<div style="margin-top:6px">' . htmlspecialchars(self::text('held_list', ['commands' => implode(', ', array_unique($held))])) . '</div>';
                }

                return [self::text('tab_title') => $html];
            }
            catch(\Throwable $e)
            {
                return [];
            }
        });
    }

    // ------------------------------------------------------------------ state

    /**
     * The run the legacy module is paused for (null when there is none): one that is preparing, running or
     * switching, or the latest one that stopped with an error and still has its backups (it waits for a rollback).
     */
    public static function run(): ?array
    {
        if(self::$state === null)
        {
            self::$state = ['run' => null, 'server' => '', 'addon' => ''];
            if(!Schema::tableExists(Store::runsTable()))
            {
                return null;
            }
            $row = Capsule::table(Store::runsTable())->where('status', '<>', Store::RUN_DRY_RUN)->orderBy('id', 'desc')->first();
            if(!$row)
            {
                return null;
            }
            $active = in_array($row->status, [Store::RUN_PREPARING, Store::RUN_RUNNING, Store::RUN_SWITCHING], true)
                || ($row->status === Store::RUN_FAILED && $row->backup_prefix && (new Backup((int)$row->id, $row->backup_prefix))->tables());
            if($active)
            {
                $reader      = Detector::reader((string)$row->source);
                self::$state = [
                    'run'    => ['id' => (int)$row->id, 'status' => (string)$row->status, 'source' => (string)$row->source],
                    'server' => $reader->serverType(),
                    'addon'  => $reader->addonName(),
                ];
            }
        }

        return self::$state['run'];
    }

    /**
     * Is the legacy module paused for this service? Its product is still on the legacy module and the run did not
     * leave it there (skipped / failed). A service the run has not listed yet (preparing, a new order) is paused too.
     */
    public static function blocks(int $serviceId): bool
    {
        $run = $serviceId > 0 ? self::run() : null;
        if(!$run)
        {
            return false;
        }
        $type = (string)Capsule::table('tblhosting')->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->where('tblhosting.id', $serviceId)->value('tblproducts.servertype');
        if(strcasecmp($type, self::$state['server']) !== 0)
        {
            return false;
        }
        $status = Capsule::table(Store::jobsTable())->where('migration_id', $run['id'])->where('kind', Store::KIND_SERVICE)
            ->where('object_id', $serviceId)->orderBy('id', 'desc')->value('status');

        return !in_array($status, [Store::JOB_FAILED, Store::JOB_SKIPPED], true);
    }

    // ------------------------------------------------------------------ module commands

    /**
     * PreModule* hook: aborts the command of a paused service; a create, renewal or upgrade is held for later
     */
    public static function onModuleCommand(string $command, array $vars): array
    {
        $params    = isset($vars['params']) && is_array($vars['params']) ? $vars['params'] : $vars;
        $serviceId = (int)($params['serviceid'] ?? $params['serviceId'] ?? 0);
        if(!self::blocks($serviceId))
        {
            return [];
        }
        $run  = self::run();
        $held = isset(self::REPLAYED[$command]) ? self::hold($run['id'], $serviceId, $command) : false;
        $note = Edition::name() . ': ' . $command . ' of service #' . $serviceId . ' paused - migration #' . $run['id'] . ' is moving it to the new module'
            . ($held ? '; held, it runs again when the migration has ended' : (isset(self::REPLAYED[$command]) ? ' (already held)' : '; run it again after the migration'));
        if(function_exists('logActivity'))
        {
            logActivity($note);
        }
        Store::log($run['id'], 'warning', 'legacy', $note, ['command' => $command], $serviceId);

        return ['abortcmd' => true];
    }

    /** Holds a command once per service (a second payment of the same renewal must not renew twice) */
    protected static function hold(int $runId, int $serviceId, string $command): bool
    {
        $exists = Capsule::table(Store::jobsTable())->where('migration_id', $runId)->where('kind', self::KIND_HELD)
            ->where('object_id', $serviceId)->where('reason', $command)->where('status', Store::JOB_PENDING)->exists();
        if($exists)
        {
            return false;
        }
        Store::addJob($runId, self::KIND_HELD, $serviceId, 0, Store::JOB_PENDING, $command, [
            'command' => $command,
            'by'      => !empty($_SESSION['adminid']) ? 'admin #' . (int)$_SESSION['adminid'] : (PHP_SAPI === 'cli' ? 'cron' : 'system'),
            'at'      => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    /**
     * Runs the held commands again - after the switch (on the new module) or the rollback (on the legacy module)
     */
    public static function replay(int $runId): array
    {
        self::reset();
        $result = ['done' => 0, 'failed' => 0];
        foreach(Store::jobsByStatus($runId, self::KIND_HELD, Store::JOB_PENDING) as $job)
        {
            $command   = (string)($job['data']['command'] ?? $job['reason']);
            $serviceId = (int)$job['object_id'];
            try
            {
                $function = self::REPLAYED[$command] ?? '';
                if(!function_exists($function) && defined('ROOTDIR'))
                {
                    require_once ROOTDIR . '/includes/modulefunctions.php';
                }
                if(!function_exists($function))
                {
                    throw new \RuntimeException('WHMCS function ' . $function . ' not available');
                }
                if(!Capsule::table('tblhosting')->where('id', $serviceId)->exists())
                {
                    throw new \RuntimeException('Service not found');
                }
                $answer = $function($serviceId);
                $ok     = $answer === 'success';
                $reason = $ok ? null : (is_string($answer) && $answer !== '' ? $answer : 'no answer');
            }
            catch(\Throwable $e)
            {
                $ok     = false;
                $reason = $e->getMessage();
            }
            Store::finishJob((int)$job['id'], $ok ? Store::JOB_DONE : Store::JOB_FAILED, $ok ? $command : $command . ': ' . $reason, $job['data'] + ['replayed_at' => date('Y-m-d H:i:s')]);
            Store::log($runId, $ok ? 'info' : 'warning', 'legacy', 'Held ' . $command . ' run again: ' . ($ok ? 'done' : 'failed (' . $reason . ') - run it from the service page'), [], $serviceId);
            $result[$ok ? 'done' : 'failed']++;
        }

        return $result;
    }

    // ------------------------------------------------------------------ requests

    /**
     * Stops the requests that would let the legacy module act. Runs when WHMCS loads the hooks - before the page.
     */
    public static function guardRequest(): void
    {
        $script = self::entryScript();
        if($script === '' || !defined('ROOTDIR'))
        {
            return;
        }
        $name      = strtolower(basename($script));
        $inModules = strpos($script, '/modules/servers/') !== false || strpos($script, '/modules/addons/') !== false;
        if(!$inModules && !in_array($name, ['clientarea.php', 'configuressl.php', 'clientsservices.php', 'addonmodules.php'], true))
        {
            return; // nearly every request ends here, without a query
        }

        if($inModules)
        {
            // the legacy module's own scripts: crons and end points that load WHMCS themselves
            if(self::run() && self::isLegacyScript($script))
            {
                self::stop(self::text('paused_script'));
            }

            return;
        }

        if($name === 'clientarea.php' && ($_GET['action'] ?? $_POST['action'] ?? '') === 'productdetails')
        {
            $serviceId = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
            $plainView = empty($_POST) && !array_diff(array_keys($_GET), self::VIEW_KEYS);
            if(!$plainView && self::blocks($serviceId))
            {
                self::stop(self::text('client'), 'clientarea.php?action=productdetails&id=' . $serviceId);
            }

            return;
        }

        if($name === 'configuressl.php' && !empty($_REQUEST['cert']))
        {
            $serviceId = (int)Capsule::table('tblsslorders')->whereRaw('MD5(id) = ?', [(string)$_REQUEST['cert']])->value('serviceid');
            if(self::blocks($serviceId))
            {
                self::stop(self::text('client'), 'clientarea.php?action=productdetails&id=' . $serviceId);
            }

            return;
        }

        if($name === 'clientsservices.php')
        {
            $serviceId = (int)($_REQUEST['id'] ?? $_REQUEST['productselect'] ?? 0);
            $legacyTab = false;
            foreach(array_keys($_REQUEST) as $key)
            {
                $legacyTab = $legacyTab || strpos((string)$key, 'mg-') === 0;
            }
            // module commands (modop) are aborted by the PreModule* hooks, which also hold a paid create / renewal
            $save = !empty($_POST) && !isset($_REQUEST['modop']);
            if(($save || $legacyTab) && self::blocks($serviceId))
            {
                $userId = (int)($_REQUEST['userid'] ?? 0);
                self::stop(self::adminText(), 'clientsservices.php?' . ($userId ? 'userid=' . $userId . '&' : '') . 'id=' . $serviceId);
            }

            return;
        }

        if($name === 'addonmodules.php' && self::run() && strcasecmp((string)($_GET['module'] ?? ''), self::$state['addon']) === 0)
        {
            self::stop(self::text('paused_script'), null, 'addonmodules.php?module=' . Writer::MODULE . '&mg-page=migration');
        }
    }

    protected static function entryScript(): string
    {
        $file = (string)($_SERVER['SCRIPT_FILENAME'] ?? '');
        if($file === '')
        {
            return '';
        }
        $real = realpath($file);

        return str_replace('\\', '/', $real ?: $file);
    }

    protected static function isLegacyScript(string $script): bool
    {
        $modules = rtrim(str_replace('\\', '/', realpath(ROOTDIR) ?: ROOTDIR), '/') . '/modules/';
        foreach(['servers/' . self::$state['server'], 'addons/' . self::$state['addon']] as $dir)
        {
            if(stripos($script, $modules . $dir . '/') === 0)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Ends the request: a line on the command line, JSON for AJAX, otherwise back to the page (where the notice
     * explains) or a short page of its own
     */
    protected static function stop(string $message, ?string $redirect = null, ?string $link = null): void
    {
        if(PHP_SAPI === 'cli')
        {
            echo $message . PHP_EOL;
            exit(0);
        }
        $ajax = strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest'
            || stripos((string)($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json') !== false
            || !empty($_REQUEST['ajax']) || !empty($_REQUEST['json']);
        if($ajax)
        {
            header('Content-Type: application/json');
            echo json_encode(['result' => 'error', 'status' => 'error', 'success' => false, 'error' => $message, 'message' => $message, 'errorMsg' => $message]);
            exit;
        }
        if($redirect !== null)
        {
            header('Location: ' . $redirect);
            exit;
        }
        http_response_code(503);
        header('Retry-After: 300');
        echo '<!doctype html><html><head><meta charset="utf-8"><title>' . htmlspecialchars(Edition::name()) . '</title></head>'
            . '<body style="font-family:sans-serif;max-width:640px;margin:60px auto;padding:0 16px;line-height:1.5">'
            . '<p>' . htmlspecialchars($message) . '</p>'
            . ($link ? '<p><a href="' . htmlspecialchars($link) . '">' . htmlspecialchars(self::text('open_migration')) . '</a></p>' : '')
            . '</body></html>';
        exit;
    }

    // ------------------------------------------------------------------ texts

    protected static function adminText(): string
    {
        $run = self::run();

        return self::text($run && $run['status'] === Store::RUN_FAILED ? 'admin_failed' : 'admin');
    }

    /** Server module language (client and admin), English when it is not loaded */
    protected static function text(string $key, array $vars = []): string
    {
        $run  = self::$state['run'] ?? null;
        $vars += ['id' => $run ? $run['id'] : '', 'module' => Edition::name()];
        $text = '';
        try
        {
            if(class_exists('\ModulesGarden\Servers\TSSGGSModule\core\Lang'))
            {
                // absoluteT returns HTML entities; the callers escape for themselves
                $text = html_entity_decode((string)\ModulesGarden\Servers\TSSGGSModule\core\Lang::absoluteT('migrationPause', $key), ENT_QUOTES, 'UTF-8');
            }
        }
        catch(\Throwable $e)
        {
            $text = '';
        }
        if($text === '' || $text === $key || strpos($text, 'migrationPause') !== false || strpos($text, '_LANG[') !== false)
        {
            $text = self::ENGLISH[$key] ?? $key;
        }
        foreach($vars as $name => $value)
        {
            $text = str_replace(':' . $name, (string)$value, $text);
        }

        return $text;
    }

    const ENGLISH = [
        'client'         => 'This certificate is being moved to our new SSL system. Changes are paused for a few minutes - please try again shortly.',
        'admin'          => 'Migration #:id is moving this service to :module. Until it ends, the legacy module is paused for it: saving the service and its module actions wait. A paid order, renewal or upgrade is held and runs automatically when the migration has ended.',
        'admin_failed'   => 'Migration #:id stopped with an error. The legacy module stays paused for this service until the migration is rolled back (Addons > :module > Migration).',
        'held_list'      => 'Held for after the migration: :commands',
        'tab_title'      => 'Migration',
        'paused_script'  => 'The legacy SSL module is paused while migration #:id moves its services to :module.',
        'open_migration' => 'Open the migration',
    ];
}
