<?php

namespace TSSGGSModule\App\Pages\Admin;

use TSSGGSModule\App\Kernel\Page;
use TSSGGSModule\App\Libs\Support\Edition;
use TSSGGSModule\App\Libs\Update\Installer;
use TSSGGSModule\App\Libs\Update\Preflight;
use TSSGGSModule\App\Libs\Update\Release;
use TSSGGSModule\App\Libs\Update\State;
use TSSGGSModule\App\Libs\Update\Workdir;
use WHMCS\Database\Capsule;

/**
 * Module update page (App\Libs\Update): the installed and the newest version, its notes, the server checks, Install
 * (or why not, with the download link), the progress of a running install, rollback and the update history.
 *
 * The install runs as three requests - download and verify, back up, swap - each a POST that redirects back here;
 * the page posts the next step by itself until the update is done or fails. These are plain form posts on purpose
 * (no htmx): the last step replaces the module's own files, and the page that follows comes from the new version.
 * For the same reason the session entry (SESSION) keeps the shape older versions wrote.
 * Only full administrators may start an update or a rollback.
 */
class ModuleUpdate extends Page
{
    const LANG       = 'admin.module_update';
    const SESSION    = 'tssggs_module_update';
    const STEPS      = ['prepare', 'backup', 'install'];

    public function index()
    {
        $session = $_SESSION[self::SESSION] ?? [];
        unset($_SESSION[self::SESSION]);

        $state = State::get();
        $data  = [
            'error'     => (string)($session['error'] ?? ''),
            'notice'    => !empty($session['notice']) ? $this->t('notice_' . $session['notice'], ['version' => (string)($session['detail'] ?? '')]) : '',
            'version'   => (string)($state['version'] ?? ''),
            'running'   => null,
            'fullAdmin' => self::fullAdmin(),
        ];

        // an install in progress: the page posts the next step by itself
        if(!empty($session['next']))
        {
            $steps = [];
            $done  = array_search($session['done'] ?? '', self::STEPS, true);
            foreach(self::STEPS as $index => $step)
            {
                $steps[] = ['key' => $step, 'state' => $done !== false && $index <= $done ? 'is-done' : ($step === $session['next'] ? 'is-running' : '')];
            }

            return $this->view('admin/moduleUpdate/index', ['running' => ['next' => (string)$session['next'], 'steps' => $steps]] + $data);
        }

        $installed = State::installedVersion();
        $available = Release::available($state);
        $checks    = $available ? Preflight::run($state, true) : [];
        $failed    = array_values(array_filter($checks, function($check) {
            return !$check['ok'] && $check['blocking'];
        }));

        return $this->view('admin/moduleUpdate/index', $data + [
            'installed'  => $installed,
            'page'       => (string)($state['page'] ?? ''),
            'checkedAt'  => !empty($state['checkedAt']) ? date('Y-m-d H:i', (int)$state['checkedAt']) : $this->t('never'),
            'checkError' => (string)($state['error'] ?? ''),
            'available'  => $available,
            'notes'      => (string)($state['notes'] ?? ''),
            'checks'     => $checks,
            'checksOk'   => $available && Preflight::passed($checks),
            // only the self-check failed (a password-protected site, a firewall): the command line can do it all
            'cliInstall' => count($failed) === 1 && $failed[0]['key'] === 'loopback' ? 'php ' . Workdir::addonDir() . '/cron/cron.php ModuleUpdate --install' : '',
            'backup'     => $this->backupVersion($installed),
            'rollbackOk' => !isset($state['rollbackSafe']) || $state['rollbackSafe'],
            'canSwap'    => Preflight::canSwap(),
            'cliRollback' => 'php ' . Workdir::addonDir() . '/cron/cron.php ModuleUpdate --rollback',
            'history'    => $this->history(),
            'recover'    => Workdir::path('recover.php'),
        ]);
    }

    /** Looks for a release now */
    public function postCheck()
    {
        return $this->run(function() {
            Release::check(true);
            $_SESSION[self::SESSION] = ['notice' => 'checked'];
        });
    }

    /** POST step=prepare|backup|install */
    public function postStep()
    {
        return $this->run(function() {
            $step = $this->request->oneOf('step', self::STEPS);
            if($step === '')
            {
                throw new \RuntimeException('Unknown step');
            }
            $result = $step === 'prepare' ? Installer::prepare() : ($step === 'backup' ? Installer::backup() : Installer::install((int)($_SESSION['adminid'] ?? 0)));
            $next   = self::STEPS[array_search($step, self::STEPS, true) + 1] ?? null;
            $_SESSION[self::SESSION] = $next ? ['next' => $next, 'done' => $step] : ['notice' => 'installed', 'detail' => $result['to'] ?? ''];
        });
    }

    public function postRollback()
    {
        return $this->run(function() {
            $result = Installer::rollback((int)($_SESSION['adminid'] ?? 0));
            $_SESSION[self::SESSION] = ['notice' => 'rolled_back', 'detail' => $result['to'] ?? ''];
        });
    }

    protected function run(callable $action)
    {
        try
        {
            if(!self::fullAdmin())
            {
                throw new \RuntimeException('Only full administrators can update the module.');
            }
            $action();
        }
        catch(\Throwable $e)
        {
            $_SESSION[self::SESSION] = ['error' => $e->getMessage()];
        }

        return $this->redirect();
    }

    /** @return array[] at, change, result */
    protected function history(): array
    {
        $rows = [];
        foreach(State::get('moduleUpdateHistory') ?: [] as $entry)
        {
            $rows[] = [
                'at'     => (string)($entry['at'] ?? ''),
                'change' => ucfirst((string)($entry['kind'] ?? '')) . ' ' . ($entry['from'] ?? '') . ' > ' . ($entry['to'] ?? ''),
                'result' => ($entry['result'] ?? '') . (!empty($entry['error']) ? ': ' . $entry['error'] : ''),
            ];
        }

        return $rows;
    }

    /** @return string version of the newest backup that is older than the installed version, '' when there is none */
    protected function backupVersion(string $installed): string
    {
        $newest = null;
        foreach(glob(Workdir::path('backups') . '/' . Edition::folder() . '-*.zip') ?: [] as $file)
        {
            $version = Installer::backupVersion($file);
            if($version && version_compare($version, $installed, '<') && (!$newest || filemtime($file) > filemtime($newest[0])))
            {
                $newest = [$file, $version];
            }
        }

        return $newest ? (string)$newest[1] : '';
    }

    /** The WHMCS role "Full Administrator" (role 1 on every install) */
    public static function fullAdmin(): bool
    {
        $adminId = (int)($_SESSION['adminid'] ?? 0);
        if(!$adminId)
        {
            return false;
        }
        $role = Capsule::table('tbladmins')->join('tbladminroles', 'tbladminroles.id', '=', 'tbladmins.roleid')
            ->where('tbladmins.id', $adminId)->first(['tbladminroles.id', 'tbladminroles.name']);

        return $role && ((int)$role->id === 1 || strcasecmp((string)$role->name, 'Full Administrator') === 0);
    }
}
