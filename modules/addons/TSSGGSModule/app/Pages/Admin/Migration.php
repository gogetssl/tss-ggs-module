<?php

namespace TSSGGSModule\App\Pages\Admin;

use TSSGGSModule\App\Kernel\Page;
use TSSGGSModule\App\Kernel\Response;
use TSSGGSModule\App\Libs\Setup\MigrationScreen;
use TSSGGSModule\App\Libs\Support\Edition;
use TSSGGSModule\App\Migration\Choice;
use TSSGGSModule\App\Migration\Engine;
use TSSGGSModule\App\Migration\Store;
use WHMCS\Database\Capsule;

/**
 * Admin page "Migration": the legacy modules found, the dry-run numbers, start / progress / result of a run, its
 * backups and the rollback. What it shows: App\Libs\Setup\MigrationScreen; templates: resources/templates/admin/migration.
 * A run is processed batch by batch: the runner script (resources/assets/js/pages/migration.js) posts "batch" until
 * the run is finished. Every action that changes something is a POST with the admin token.
 */
class Migration extends Page
{
    const LANG       = 'admin.migration';
    const BODY_CLASS = 'tssggs-migration';

    public function index()
    {
        return $this->view('admin/migration/index', (new MigrationScreen($this->lang))->data());
    }

    /** POST source, variant, map[ref]: prepares the run and processes the first batch */
    public function postStart()
    {
        try
        {
            $source = $this->source();
            Choice::set($source, Choice::MIGRATE);
            $engine  = new Engine($source);
            // the options are on unless the button pressed switches one off (variant)
            $variant = $this->request->string('variant');
            $map     = [];
            foreach((array)$this->request->input('map', []) as $ref => $target)
            {
                $map[(string)$ref] = (string)$target;
            }
            $runId = $engine->start($this->admin(), [
                'fetch_cpanel_keys' => $variant !== 'nokeys',
                'copy_templates'    => true,
                'backup_logs'       => true,
                'deactivate_legacy' => $variant !== 'keeplegacy',
                'product_map'       => $map,
            ]);
            $engine->runBatch($runId);
        }
        catch(\Throwable $e)
        {
            $_SESSION[MigrationScreen::SESSION_ERROR] = $e->getMessage();
        }

        return $this->back();
    }

    /** POST id: one batch; the progress as JSON for the runner */
    public function postBatch()
    {
        try
        {
            $run = Store::getRun($this->request->int('id'));
            if(!$run)
            {
                throw new \RuntimeException('Unknown migration');
            }

            return Response::json(['status' => 'success', 'progress' => $this->progress((new Engine($run['source']))->runBatch((int)$run['id']))]);
        }
        catch(\Throwable $e)
        {
            return Response::json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /** GET id: the progress only */
    public function status()
    {
        $run = Store::getRun($this->request->int('id'));
        if(!$run)
        {
            return Response::json(['status' => 'error', 'message' => 'Unknown migration']);
        }

        return Response::json(['status' => 'success', 'progress' => $this->progress((new Engine($run['source']))->status((int)$run['id']))]);
    }

    public function postRollback()
    {
        return $this->change(function() {
            Engine::rollback($this->request->int('id'), $this->admin());
        });
    }

    public function postDeleteBackups()
    {
        return $this->change(function() {
            Engine::deleteBackups($this->request->int('id'), $this->admin());
        });
    }

    public function postExtendBackups()
    {
        return $this->change(function() {
            Engine::extendBackups($this->request->int('id'));
        });
    }

    /** POST source: the legacy module stays as it is ("install standalone") */
    public function postSkip()
    {
        return $this->change(function() {
            Choice::set($this->source(), Choice::SKIP);
        });
    }

    /** GET id: the run's log as CSV */
    public function log()
    {
        $id = $this->request->int('id');

        return Response::download(Engine::logCsv($id), 'migration-' . $id . '-log.csv');
    }

    /** GET id: the run's error report (text) to send to support */
    public function errorReport()
    {
        $id = $this->request->int('id');

        return Response::download(Engine::errorReport($id), strtolower(str_replace(' ', '-', Edition::name())) . '-migration-' . $id . '-error-report.txt', 'text/plain; charset=utf-8');
    }

    /** GET source: the dry-run report as CSV (one row per service) */
    public function report()
    {
        try
        {
            $source = $this->source();
        }
        catch(\Throwable $e)
        {
            return $this->redirect();
        }
        $report = (new Engine($source))->analyse();
        $out    = fopen('php://temp', 'w+');
        fputcsv($out, ['group', 'service_id', 'product_id', 'kind', 'legacy_status', 'target_status', 'remote_id', 'autoinstall', 'reason', 'warnings']);
        foreach($report['services'] as $group => $rows)
        {
            foreach($rows as $r)
            {
                fputcsv($out, [$group, $r['service_id'], $r['product_id'], $r['kind'], $r['legacy_status'], $r['target_status'], $r['remote_id'], $r['autoinstall'] ? 'yes' : '', (string)$r['reason'], implode(' | ', $r['warnings'])]);
            }
        }
        foreach($report['products'] as $p)
        {
            fputcsv($out, ['product', '', $p['pid'], $p['mapped'] ? 'mapped' : 'not mapped', '', '', $p['remote_product']['id'] ?? '', '', $p['legacy_product_ref'], implode(' | ', $p['warnings'])]);
        }
        fputcsv($out, ['credentials', '', '', $report['summary']['credentials'], '', '', '', '', '', '']);
        rewind($out);
        $csv = (string)stream_get_contents($out);
        fclose($out);

        return Response::download($csv, 'migration-' . $source . '-dry-run.csv');
    }

    /* ---- helpers ---- */

    /** Runs a change; what went wrong is shown on the page the request came from */
    protected function change(callable $change): Response
    {
        try
        {
            $change();
        }
        catch(\Throwable $e)
        {
            $_SESSION[MigrationScreen::SESSION_ERROR] = $e->getMessage();
        }

        return $this->back();
    }

    /** Back to where the form was: the wizard's migration step, or this page */
    protected function back(): Response
    {
        return $this->request->string('return') === 'wizard' ? Response::redirect(self::pageUrl('configuration', 'step7')) : $this->redirect();
    }

    /** The legacy module of this edition; no other one can be migrated */
    protected function source(): string
    {
        $source = strtolower($this->request->string('source'));
        if($source !== Edition::get())
        {
            throw new \InvalidArgumentException('Unknown legacy source');
        }

        return $source;
    }

    /** "name (#id)" of the logged in admin, for the run's log */
    protected function admin(): string
    {
        $id   = (int)($_SESSION['adminid'] ?? 0);
        $name = $id ? (string)Capsule::table('tbladmins')->where('id', $id)->value('username') : '';

        return $name !== '' ? $name . ' (#' . $id . ')' : 'admin';
    }

    protected function progress(array $status): array
    {
        return [
            'id'       => (int)$status['run']['id'],
            'status'   => $status['run']['status'],
            'percent'  => $status['percent'],
            'jobs'     => $status['jobs'],
            'finished' => $status['finished'],
            'stale'    => $status['stale'],
            'error'    => $status['run']['last_error'],
        ];
    }
}
