<?php

namespace ModulesGarden\TSSGGSModule\App\Http\Admin;

use ModulesGarden\TSSGGSModule\App\Migration\Choice;
use ModulesGarden\TSSGGSModule\App\Migration\Engine;
use ModulesGarden\TSSGGSModule\App\Migration\Store;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Migration\Index\MigrationContainer;
use ModulesGarden\TSSGGSModule\Core\Contracts\Controllers\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Helper;
use ModulesGarden\TSSGGSModule\Core\Http\AbstractController;
use ModulesGarden\TSSGGSModule\Core\Http\JsonResponse;

/**
 * Legacy module migration page and its actions.
 * Mutating actions carry the WHMCS admin token (link token or POST field, see requireToken()).
 */
class Migration extends AbstractController implements AdminAreaInterface
{
    public function index()
    {
        return Helper\view()->addElement(MigrationContainer::class);
    }

    /** POST source=ggs|tss: prepares the run and processes the first batch */
    public function start()
    {
        try
        {
            $this->requireToken();
            $source = $this->source();
            Choice::set($source, Choice::MIGRATE);
            $engine = new Engine($source);
            // options are 'on' unless the link switches them off (opt_keys=0, opt_logs=0, opt_deactivate=0)
            $runId  = $engine->start($this->admin(), [
                'fetch_cpanel_keys' => ($_REQUEST['opt_keys'] ?? '1') !== '0',
                'copy_templates'    => false,
                'backup_logs'       => ($_REQUEST['opt_logs'] ?? '1') !== '0',
                'deactivate_legacy' => ($_REQUEST['opt_deactivate'] ?? '1') !== '0',
            ]);
            $engine->runBatch($runId);
        }
        catch(\Throwable $e)
        {
            $_SESSION['tssggs_migration_error'] = $e->getMessage();
        }

        return Helper\redirect('migration', 'index');
    }

    /** POST id: one batch; JSON progress for the runner component */
    public function batch()
    {
        try
        {
            $this->requireToken();
            $run = Store::getRun((int)($_POST['id'] ?? 0));
            if(!$run)
            {
                throw new \RuntimeException('Unknown migration');
            }
            $status = (new Engine($run['source']))->runBatch((int)$run['id']);

            return new JsonResponse(['status' => 'success', 'progress' => $this->progress($status)]);
        }
        catch(\Throwable $e)
        {
            return (new JsonResponse())->withError($e->getMessage());
        }
    }

    /** GET id: progress only */
    public function status()
    {
        $run = Store::getRun((int)($_REQUEST['id'] ?? 0));
        if(!$run)
        {
            return (new JsonResponse())->withError('Unknown migration');
        }

        return new JsonResponse(['status' => 'success', 'progress' => $this->progress((new Engine($run['source']))->status((int)$run['id']))]);
    }

    public function rollback()
    {
        $this->mutate(function() { Engine::rollback((int)$_REQUEST['id'], $this->admin()); });

        return Helper\redirect('migration', 'index');
    }

    public function deleteBackups()
    {
        $this->mutate(function() { Engine::deleteBackups((int)$_REQUEST['id'], $this->admin()); });

        return Helper\redirect('migration', 'index');
    }

    public function extendBackups()
    {
        $this->mutate(function() { Engine::extendBackups((int)$_REQUEST['id']); });

        return Helper\redirect('migration', 'index');
    }

    public function skip()
    {
        $this->mutate(function() { Choice::set($this->source(), Choice::SKIP); });

        return Helper\redirect('migration', 'index');
    }

    /** GET id: the run's log as CSV */
    public function log()
    {
        $id  = (int)($_REQUEST['id'] ?? 0);
        $csv = Engine::logCsv($id);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="tssggs-migration-' . $id . '-log.csv"');
        header('X-Content-Type-Options: nosniff');
        echo $csv;
        exit;
    }

    /** GET source: dry-run report as CSV (one row per service) */
    public function report()
    {
        $source = $this->source();
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
        rewind($out);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="tssggs-migration-' . $source . '-dry-run.csv"');
        header('X-Content-Type-Options: nosniff');
        echo stream_get_contents($out);
        exit;
    }

    // ---- helpers ---- //

    protected function mutate(callable $fn): void
    {
        try
        {
            $this->requireToken();
            $fn();
        }
        catch(\Throwable $e)
        {
            $_SESSION['tssggs_migration_error'] = $e->getMessage();
        }
    }

    protected function source(): string
    {
        $source = strtolower((string)($_REQUEST['source'] ?? ''));
        if(!in_array($source, ['ggs', 'tss'], true))
        {
            throw new \InvalidArgumentException('Unknown legacy source');
        }

        return $source;
    }

    protected function admin(): string
    {
        $id   = (int)($_SESSION['adminid'] ?? 0);
        $name = $id ? (string)\WHMCS\Database\Capsule::table('tbladmins')->where('id', $id)->value('username') : '';

        return $name !== '' ? $name . ' (#' . $id . ')' : 'admin';
    }

    public static function token(): string
    {
        return function_exists('generate_token') ? (string)\generate_token('plain') : '';
    }

    protected function requireToken(): void
    {
        // WHMCS admin links carry the token in the query string (generate_token('link')); the runner posts it
        $token = (string)($_REQUEST['token'] ?? '');
        $valid = (string)($_SESSION['tkval'] ?? '');
        if($token === '' || $valid === '' || !hash_equals($valid, $token))
        {
            throw new \RuntimeException('Invalid or expired security token. Reload the page and try again.');
        }
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
