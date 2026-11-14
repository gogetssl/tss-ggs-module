<?php

namespace ModulesGarden\TSSGGSModule\App\Migration;

use ModulesGarden\TSSGGSModule\App\Libs\Markups;
use ModulesGarden\TSSGGSModule\App\Libs\Cpanel;
use ModulesGarden\TSSGGSModule\App\Libs\EmailCatalog;
use ModulesGarden\TSSGGSModule\App\Libs\EmailManager;
use ModulesGarden\TSSGGSModule\App\Libs\LegacyEmailImport;
use ModulesGarden\TSSGGSModule\App\Migration\Readers\LegacyReaderInterface;
use ModulesGarden\TSSGGSModule\App\Migration\Support\Schema;
use WHMCS\Database\Capsule;

/**
 * Orchestrates a legacy → unified migration:
 *
 *   dryRun()  read-only report
 *   start()   backups, settings, products, job list              (status preparing → running)
 *   runBatch() N services per call, resumable                     (running → switching when no pending jobs)
 *   runBatch() in switching: re-read changed services, flip products/orders, rescan, deactivate legacy addon,
 *             verify                                              (switching → completed)
 *   rollback() restore from backups, undo unified rows, reactivate the legacy addon
 *
 * No gateway calls. The engine depends on WHMCS Capsule and the module's own tables only.
 */
class Engine
{
    const BATCH_SIZE        = 25;
    const STALE_AFTER_SEC   = 600;
    const PHASE_PREPARE     = 'prepare';
    const PHASE_SERVICES    = 'services';
    const PHASE_SWITCH      = 'switch';
    const PHASE_ROLLBACK    = 'rollback';

    /** @var LegacyReaderInterface */
    /** exceptions (skipped / failed services) kept in the run summary */
    const SUMMARY_EXCEPTIONS = 150;

    protected $reader;
    protected $source;
    protected $mapper;

    public function __construct(string $source)
    {
        $this->source = $source;
        $this->reader = Detector::reader($source);
        $this->mapper = new ProductMapper();
        if(method_exists($this->reader, 'setMapper'))
        {
            $this->reader->setMapper($this->mapper);
        }
    }

    public function reader(): LegacyReaderInterface
    {
        return $this->reader;
    }

    // =================================================================== dry run

    /**
     * Full read-only analysis. Stored as a dry_run row so the report can be downloaded later.
     */
    public function dryRun(string $startedBy = 'admin'): array
    {
        $report = $this->analyse();
        $runId  = Store::createRun($this->source, $this->reader->version(), $startedBy, ['dry_run' => true], Store::RUN_DRY_RUN);
        Store::updateRun($runId, ['summary' => $report['summary'], 'finished_at' => date('Y-m-d H:i:s')]);
        Store::log($runId, 'info', 'dry_run', 'Dry run completed', $report['summary']);
        $report['run_id'] = $runId;

        return $report;
    }

    /**
     * Read-only analysis (nothing is stored). dryRun() stores it as a run for later download.
     */
    public function analyse(): array
    {
        $support  = $this->reader->versionSupport();
        $products = [];
        $services = ['migrate' => [], 'awaiting' => [], 'preordered' => [], 'inactive' => [], 'skipped' => [], 'unsupported' => []];
        $warnings = [];

        foreach($this->reader->productIds() as $pid)
        {
            $p = $this->reader->readProduct($pid);
            $p['mapped'] = $p['remote_product'] !== null && empty($p['unsupported']);
            if(!$p['remote_product'] && empty($p['unsupported']))
            {
                $p['warnings'][] = 'No gateway product found for "' . $p['legacy_product_ref'] . '"; services of this product stay on the legacy module';
            }
            $p['remote_product'] = $p['remote_product'] ? ['id' => $p['remote_product']->remoteId, 'name' => $p['remote_product']->name] : null;
            $products[$pid] = $p;
        }

        foreach($this->reader->serviceIds() as $serviceId)
        {
            $rec = $this->reader->readService($serviceId);
            $row = [
                'service_id' => $serviceId, 'product_id' => $rec['product_id'] ?? 0, 'kind' => $rec['kind'] ?? 'ssl',
                'legacy_status' => $rec['legacy_status'] ?? '', 'target_status' => $rec['target_status'] ?? '',
                'remote_id' => $rec['remote_id'] ?? '', 'autoinstall' => !empty($rec['autoinstall']), 'warnings' => $rec['warnings'] ?? [], 'reason' => $rec['skip_reason'] ?? null,
            ];
            if(!empty($rec['product_id']) && isset($products[$rec['product_id']]) && !$products[$rec['product_id']]['mapped'] && !$rec['skip_reason'])
            {
                $row['reason'] = 'Product "' . $products[$rec['product_id']]['name'] . '" is not mapped to a gateway product';
                $services['skipped'][] = $row;
                continue;
            }
            if(empty($rec['skip_reason']) && !empty($rec['order_payload']) && (string)($rec['order_payload']['product']['id'] ?? '') === '')
            {
                $row['reason'] = 'Gateway product not resolved for this service (code "' . ($rec['product_code'] ?? '') . '")';
                $services['skipped'][] = $row;
                continue;
            }
            if(($rec['kind'] ?? '') === 'unsupported')
            {
                $services['unsupported'][] = $row;
            }
            elseif(!empty($rec['skip_reason']))
            {
                $services['skipped'][] = $row;
            }
            elseif(!empty($rec['preordered']))
            {
                $services['preordered'][] = $row;
            }
            elseif(($rec['target_status'] ?? '') === 'Awaiting Configuration')
            {
                $services['awaiting'][] = $row;
            }
            elseif(!in_array($rec['target_status'] ?? '', ['Configuration Submitted', 'Completed'], true))
            {
                $services['inactive'][] = $row;
            }
            else
            {
                $services['migrate'][] = $row;
            }
            foreach($rec['warnings'] ?? [] as $w)
            {
                $warnings[$w] = ($warnings[$w] ?? 0) + 1;
            }
        }

        $legacyTables = $this->reader->tables();
        $summary = [
            'source'          => $this->source,
            'label'           => $this->reader->label(),
            'version'         => $this->reader->version(),
            'support'         => $support,
            'products'        => ['total' => count($products), 'mapped' => count(array_filter($products, function($p) { return $p['mapped']; })), 'unsupported' => count(array_filter($products, function($p) { return !empty($p['unsupported']); }))],
            'services'        => array_map('count', $services),
            'counts'          => $this->reader->counts(),
            'warnings'        => $warnings,
            'backup_tables'   => $legacyTables,
            'backup_bytes'    => Backup::estimateBytes($legacyTables),
            'settings_filled' => array_keys(array_filter($this->reader->readSettings()['sslSettings'] ?? [], function($v) { return $v !== ''; })),
            'credentials'     => Credentials::state($this->source),
            'cron_lines'      => $this->reader->cronLines(),
            'addon_active'    => LegacyModuleControl::isActive($this->reader->addonName()),
            'mapper_has_vendor' => $this->mapper->hasVendor(strtoupper($this->source)),
        ];

        return ['summary' => $summary, 'products' => array_values($products), 'services' => $services];
    }

    // =================================================================== start

    /**
     * Creates the run: backups, settings, product rewrite, job list. Returns the run id. Fast (seconds).
     */
    public function start(string $startedBy, array $options = []): int
    {
        if(!self::lock())
        {
            throw new \RuntimeException('Another migration is being started or processed. Please wait a moment.');
        }
        try
        {
            if(Store::activeRun())
            {
                throw new \RuntimeException('Another migration is still running. Resume or roll it back first.');
            }
            // the latest run stopped with an error and left its changes behind: a new run would take a backup of changed data
            $latest = Capsule::table(Store::runsTable())->where('status', '<>', Store::RUN_DRY_RUN)->orderBy('id', 'desc')->first();
            if($latest && $latest->status === Store::RUN_FAILED && $latest->backup_prefix && (new Backup((int)$latest->id, $latest->backup_prefix))->tables())
            {
                throw new \RuntimeException('Migration #' . (int)$latest->id . ' stopped with an error. Roll it back before starting a new one.');
            }

            // every legacy product without a gateway match needs the administrator's choice: an alternative product,
            // or "keep as a record" - the legacy module is not kept for them
            $map     = array_change_key_case(array_filter(array_map('strval', (array)($options['product_map'] ?? [])), 'strlen'), CASE_LOWER);
            $missing = [];
            foreach($this->unmatched() as $item)
            {
                if(!isset($map[strtolower($item['ref'])]))
                {
                    $missing[] = $item['label'];
                }
            }
            if($missing)
            {
                throw new \RuntimeException('Choose an alternative product or "keep as a record" for: ' . implode(', ', array_slice($missing, 0, 20)) . (count($missing) > 20 ? ' ...' : ''));
            }
            $options['product_map'] = $map;
            $this->mapper->setOverrides($map);

            return $this->prepare($startedBy, $options);
        }
        finally
        {
            self::unlock();
        }
    }

    /**
     * Legacy products (and TSS order codes) the gateway has no product for, with the services that use them.
     * Each needs a choice before a run: an alternative gateway product (renewals are ordered as that product) or
     * "keep as a record" (App\Libs\RecordOnly). Read without the run's choices.
     * [['ref', 'label', 'names' => [], 'pids' => [], 'unsupported' => bool, 'services' => ['active', 'awaiting', 'other']]]
     */
    public function unmatched(): array
    {
        $mapper = new ProductMapper();
        $saved  = $this->mapper;
        $this->mapper = $mapper;
        if(method_exists($this->reader, 'setMapper'))
        {
            $this->reader->setMapper($mapper);
        }
        try
        {
            $list = [];
            foreach($this->reader->productIds() as $pid)
            {
                $p = $this->reader->readProduct($pid);
                if($p['remote_product'] && empty($p['unsupported']))
                {
                    continue;
                }
                $ref = strtolower(trim((string)$p['legacy_product_ref'])) ?: 'product-' . $pid;
                if(!isset($list[$ref]))
                {
                    $list[$ref] = ['ref' => $ref, 'label' => '', 'names' => [], 'pids' => [], 'unsupported' => !empty($p['unsupported']), 'services' => ['active' => 0, 'awaiting' => 0, 'other' => 0]];
                }
                $list[$ref]['names'][] = (string)$p['name'];
                $list[$ref]['pids'][]  = (int)$pid;
                foreach(Capsule::table('tblhosting')->leftJoin('tblsslorders', 'tblsslorders.serviceid', '=', 'tblhosting.id')->where('tblhosting.packageid', $pid)
                    ->selectRaw('tblsslorders.status AS s, COUNT(DISTINCT tblhosting.id) AS n')->groupBy('tblsslorders.status')->get() as $row)
                {
                    $key = in_array($row->s, ['Configuration Submitted', 'Completed'], true) ? 'active' : ($row->s === 'Awaiting Configuration' ? 'awaiting' : 'other');
                    $list[$ref]['services'][$key] += (int)$row->n;
                }
            }
            // TSS: an order code of a service whose product maps
            if(method_exists($this->reader, 'serviceCodes'))
            {
                foreach($this->reader->serviceCodes() as $code => $n)
                {
                    if(!isset($list[$code]) && !$mapper->forTss($code))
                    {
                        $list[$code] = ['ref' => $code, 'label' => '', 'names' => [], 'pids' => [], 'unsupported' => false, 'services' => ['active' => 0, 'awaiting' => 0, 'other' => (int)$n]];
                    }
                }
            }
            foreach($list as $ref => $item)
            {
                $list[$ref]['label'] = ($item['names'] ? implode(' / ', array_unique($item['names'])) . ' ' : '') . '(' . $ref . ')';
            }

            return array_values($list);
        }
        finally
        {
            $this->mapper = $saved;
            if(method_exists($this->reader, 'setMapper'))
            {
                $this->reader->setMapper($saved);
            }
        }
    }

    /** Gateway products of this source's vendor, for the choice */
    public function alternatives(): array
    {
        $out = [];
        foreach($this->mapper->vendorProducts(strtoupper($this->source)) as $product)
        {
            $out[] = ['id' => (string)$product->remoteId, 'name' => (string)$product->name, 'brand' => (string)$product->brand, 'validation' => (string)$product->validation];
        }

        return $out;
    }

    protected function prepare(string $startedBy, array $options): int
    {
        $support = $this->reader->versionSupport();
        if(!$support['supported'])
        {
            throw new \RuntimeException('Legacy module version not supported: ' . $support['reason']);
        }
        if(!$this->mapper->hasVendor(strtoupper($this->source)))
        {
            throw new \RuntimeException('Gateway products for ' . strtoupper($this->source) . ' are not synced yet (complete the API connection step first).');
        }

        $options = array_merge(['batch_size' => self::BATCH_SIZE, 'fetch_cpanel_keys' => true, 'copy_templates' => true, 'backup_logs' => true, 'deactivate_legacy' => true], $options);
        $runId   = Store::createRun($this->source, $this->reader->version(), $startedBy, $options, Store::RUN_PREPARING);
        $token   = bin2hex(random_bytes(16));
        Store::updateRun($runId, ['lock_token' => $token, 'started_at' => date('Y-m-d H:i:s'), 'heartbeat_at' => date('Y-m-d H:i:s')]);
        $this->log($runId, 'info', self::PHASE_PREPARE, 'Migration started', ['options' => $options, 'version' => $this->reader->version()]);

        try
        {
            // 1. the legacy module stops first, so the backups below are one consistent picture (its cron must not
            //    write half way through them). What the pause changes is backed up before it - a rollback puts it
            //    back: the WHMCS hook lists (its hooks are not loaded while it is out of them) and its schedule.
            $backup   = new Backup($runId);
            // known from the first copy on, so a failure half way can be rolled back
            Store::updateRun($runId, ['backup_prefix' => $backup->prefix(), 'backup_expires_at' => date('Y-m-d H:i:s', strtotime('+' . Store::BACKUP_RETENTION_DAYS . ' days'))]);
            $tables   = [];
            $tables[] = $backup->copyRows('tblconfiguration', function($q) { $q->whereIn('setting', self::HOOK_LISTS); });
            $early    = $this->reader->freezeTables();
            foreach($early as $table)
            {
                $tables[] = $backup->copyTable($table);
            }
            $hookLists = $this->pauseLegacyHooks();
            $frozen    = $this->reader->freeze();
            LegacyBlock::reset();
            $this->log($runId, 'info', self::PHASE_PREPARE, 'Legacy module paused until the run ends: its hooks, schedule, scripts, client and admin actions and module commands', ['hook_lists' => array_keys($hookLists), 'frozen' => $frozen]);

            // 2. backups
            $skipLog = $options['backup_logs'] ? [] : ['SSLCENTER_logs', 'mod_sslstore_logs', 'mod_autoinstallssl_logs'];
            foreach($this->reader->tables() as $table)
            {
                if(in_array($table, $skipLog, true) || in_array($table, $early, true))
                {
                    continue;
                }
                $tables[] = $backup->copyTable($table);
            }
            $serverType = $this->reader->serverType();
            $modules    = $this->reader->orderModules();
            $pids       = $this->reader->productIds();
            $tables[]   = $backup->copyRows('tblproducts', function($q) use ($serverType) { $q->where('servertype', $serverType); });
            $tables[]   = $backup->copyRows('tblsslorders', function($q) use ($modules) { $q->whereIn('module', $modules); });
            $gids       = $pids ? array_map('intval', Capsule::table('tblproductconfiglinks')->whereIn('pid', $pids)->pluck('gid')->all()) : [];
            $tables[]   = $backup->copyRows('tblproductconfiggroups', function($q) use ($gids) { $q->whereIn('id', $gids ?: [0]); });
            $tables[]   = $backup->copyRows('tblproductconfigoptions', function($q) use ($gids) { $q->whereIn('gid', $gids ?: [0]); });
            $optionIds  = $gids ? array_map('intval', Capsule::table('tblproductconfigoptions')->whereIn('gid', $gids)->pluck('id')->all()) : [];
            $tables[]   = $backup->copyRows('tblproductconfigoptionssub', function($q) use ($optionIds) { $q->whereIn('configid', $optionIds ?: [0]); });
            // the prices of those options (code signing: the legacy delivery choices get the gateway fees)
            $subIds     = $optionIds ? array_map('intval', Capsule::table('tblproductconfigoptionssub')->whereIn('configid', $optionIds)->pluck('id')->all()) : [];
            $tables[]   = $backup->copyRows('tblpricing', function($q) use ($subIds) { $q->where('type', 'configoptions')->whereIn('relid', $subIds ?: [0]); });
            $tables[]   = $backup->copyRows('tbladdonmodules', function($q) { $q->whereIn('module', ['configuration_' . Writer::MODULE, $this->reader->addonName()]); });
            if(!empty($options['copy_templates']))
            {
                // the unified e-mail templates the copy may overwrite
                EmailManager::ensureAll();
                $templateNames = array_map([EmailManager::class, 'name'], array_keys(EmailCatalog::all()));
                $tables[]      = $backup->copyRows('tblemailtemplates', function($q) use ($templateNames) { $q->whereIn('name', $templateNames); });
            }
            $tables     = array_values(array_filter($tables));

            $maxOrderId = (int)Capsule::table('tblsslorders')->max('id');
            Store::updateRun($runId, [
                'backup_prefix'     => $backup->prefix(),
                'backup_expires_at' => date('Y-m-d H:i:s', strtotime('+' . Store::BACKUP_RETENTION_DAYS . ' days')),
                'snapshot_at'       => date('Y-m-d H:i:s'),
                'summary'           => ['backups' => $backup->tables(), 'snapshot_max_order_id' => $maxOrderId, 'products' => [], 'settings_filled' => [], 'hook_lists' => $hookLists],
            ]);
            $this->log($runId, 'info', self::PHASE_PREPARE, 'Backups written', ['tables' => $tables]);

            // every service is looked up by its id in these tables; without an index each lookup reads the whole
            // table and a large install slows down with its own size. Added for the run, dropped when it ends.
            $indexes = $this->addHelperIndexes();
            if($indexes)
            {
                Store::mergeSummary($runId, ['helper_indexes' => $indexes]);
                $this->log($runId, 'info', self::PHASE_PREPARE, 'Lookup indexes added for the run', ['indexes' => $indexes]);
            }

            // 2. settings
            $writer = new Writer($runId, $this->source);
            $filled = $writer->writeSettings($this->reader->readSettings());
            $copied = Credentials::applyIfEmpty($this->source);
            if($copied === 'copied')
            {
                $filled[] = 'credentials.' . $this->source;
                $this->log($runId, 'info', self::PHASE_PREPARE, 'Gateway credentials copied from the legacy module');
            }
            elseif($copied !== '')
            {
                $this->log($runId, 'warning', self::PHASE_PREPARE, 'Legacy gateway credentials not reusable, enter them in Settings', ['error' => $copied]);
            }
            Store::addJob($runId, Store::KIND_SETTINGS, 0, 0, Store::JOB_DONE, null, ['filled' => $filled]);
            $this->log($runId, 'info', self::PHASE_PREPARE, 'Settings merged', ['filled' => $filled]);

            // 2b. the e-mail texts of the legacy module (D5, automatic): only texts whose merge fields the unified module fills;
            // the rest stays at the unified default and is listed in the log and on the Emails page
            if(!empty($options['copy_templates']))
            {
                $plan = LegacyEmailImport::auto($this->source);
                $copy = LegacyEmailImport::import($plan['ids']);
                Store::addJob($runId, Store::KIND_SETTINGS, 0, 0, Store::JOB_DONE, null, ['template_ids' => $copy['created_ids']]);
                $this->log($runId, 'info', self::PHASE_PREPARE, 'E-mail templates copied from the legacy module', ['templates' => $copy['copied'], 'new_rows' => count($copy['created_ids'])]);
                foreach($plan['skipped'] as $skip)
                {
                    $this->log($runId, 'info', self::PHASE_PREPARE, 'E-mail template not copied: ' . $skip['legacy'], ['unified' => $skip['key'], 'reason' => $skip['reason'], 'fields' => $skip['fields']]);
                }
            }

            // 3. products (configoptions + SAN option rename; servertype flips in the switch phase)
            $migratedPids = [];
            $recordPids   = [];
            foreach($pids as $pid)
            {
                $p   = $this->reader->readProduct($pid);
                $ref = strtolower(trim((string)$p['legacy_product_ref'])) ?: 'product-' . $pid;
                // no gateway product, kept as a record (the administrator's choice before the run)
                if((!$p['remote_product'] || !empty($p['unsupported'])) && $this->mapper->isRecord($ref))
                {
                    $data           = $writer->writeRecordProduct($p);
                    $migratedPids[] = $pid;
                    $recordPids[]   = $pid;
                    Store::addJob($runId, Store::KIND_PRODUCT, $pid, 0, Store::JOB_DONE, null, $data + ['name' => $p['name']]);
                    $this->log($runId, 'info', self::PHASE_PREPARE, 'Product kept as a record (no gateway product)', ['pid' => $pid, 'name' => $p['name'], 'ref' => $ref]);
                    continue;
                }
                if(!empty($p['unsupported']))
                {
                    Store::addJob($runId, Store::KIND_PRODUCT, $pid, 0, Store::JOB_SKIPPED, 'Unsupported product type');
                    continue;
                }
                if(!$p['remote_product'])
                {
                    Store::addJob($runId, Store::KIND_PRODUCT, $pid, 0, Store::JOB_SKIPPED, 'No gateway product for "' . $p['legacy_product_ref'] . '"');
                    $this->log($runId, 'warning', self::PHASE_PREPARE, 'Product not mapped, left on legacy module', ['pid' => $pid, 'ref' => $p['legacy_product_ref'], 'name' => $p['name']]);
                    continue;
                }
                $data = $writer->writeProduct($p, $p['remote_product']);
                $migratedPids[] = $pid;
                Store::addJob($runId, Store::KIND_PRODUCT, $pid, 0, Store::JOB_DONE, null, $data);
                $this->log($runId, 'info', self::PHASE_PREPARE, 'Product prepared', ['pid' => $pid, 'name' => $p['name'], 'remote' => $p['remote_product']->remoteId, 'renamed_options' => count($data['renamed_options'])]);
            }
            Store::mergeSummary($runId, ['products' => $migratedPids, 'record_products' => $recordPids, 'settings_filled' => $filled]);

            // client markups of the migrated products (GGS)
            if(method_exists($this->reader, 'readMarkups'))
            {
                $rules = array_values(array_filter($this->reader->readMarkups(), function($rule) use ($migratedPids) {
                    return in_array((int)$rule['product_id'], $migratedPids, true);
                }));
                $ids = $writer->writeMarkups($rules);
                Store::addJob($runId, Store::KIND_SETTINGS, 0, 0, Store::JOB_DONE, null, ['markup_ids' => $ids]);
                $this->log($runId, 'info', self::PHASE_PREPARE, 'Client markups copied', ['rules' => count($ids), 'legacy_rules' => count($rules)]);
            }

            // 4. service job list
            $n = 0;
            foreach($this->reader->serviceIds() as $serviceId)
            {
                Store::addJob($runId, Store::KIND_SERVICE, $serviceId);
                $n++;
            }
            Store::updateRun($runId, ['status' => Store::RUN_RUNNING, 'heartbeat_at' => date('Y-m-d H:i:s')]);
            $this->log($runId, 'info', self::PHASE_PREPARE, 'Job list created', ['services' => $n, 'products' => count($migratedPids)]);
        }
        catch(\Throwable $e)
        {
            Store::updateRun($runId, ['status' => Store::RUN_FAILED, 'last_error' => $e->getMessage(), 'finished_at' => date('Y-m-d H:i:s')]);
            $this->log($runId, 'error', self::PHASE_PREPARE, 'Preparation failed: ' . $e->getMessage(), ['trace' => substr($e->getTraceAsString(), 0, 2000)]);
            $rolledBack = $this->autoRollback($runId, 'Preparation failed: ' . $e->getMessage());
            throw new \RuntimeException('Preparation failed: ' . $e->getMessage() . ($rolledBack ? ' - the migration was rolled back automatically; the error report is on this page.' : ''), 0, $e);
        }

        return $runId;
    }

    // =================================================================== batches

    /**
     * Processes up to batch_size pending services, or runs the switch phase when none are left.
     * Returns the progress array for the UI / CLI.
     */
    public function runBatch(int $runId): array
    {
        $run = Store::getRun($runId);
        if(!$run)
        {
            throw new \RuntimeException('Migration #' . $runId . ' not found');
        }
        if(!in_array($run['status'], [Store::RUN_RUNNING, Store::RUN_SWITCHING], true))
        {
            return $this->status($runId);
        }
        // another runner is working on this migration: only report the progress
        if(!self::lock())
        {
            return $this->status($runId) + ['busy' => true];
        }
        try
        {
            // the other runner may have finished the run while this one waited
            $run = Store::getRun($runId);
            if(!$run || !in_array($run['status'], [Store::RUN_RUNNING, Store::RUN_SWITCHING], true))
            {
                return $this->status($runId);
            }
            // the administrator's choices for products without a gateway match (made at the start)
            $this->mapper->setOverrides((array)($run['options']['product_map'] ?? []));

            return $this->processBatch($runId, $run);
        }
        finally
        {
            self::unlock();
        }
    }

    protected function processBatch(int $runId, array $run): array
    {
        Store::updateRun($runId, ['heartbeat_at' => date('Y-m-d H:i:s')]);
        $writer   = new Writer($runId, $this->source);
        $products = array_map('intval', $run['summary']['products'] ?? []);
        $jobs     = Store::pendingJobs($runId, Store::KIND_SERVICE, (int)($run['options']['batch_size'] ?? self::BATCH_SIZE));

        if($run['status'] === Store::RUN_RUNNING && $jobs)
        {
            foreach($jobs as $job)
            {
                $this->migrateService($runId, $job['id'], (int)$job['object_id'], $writer, $products, (bool)$run['options']['fetch_cpanel_keys']);
            }
            if(!Store::pendingJobs($runId, Store::KIND_SERVICE, 1))
            {
                $counts = Store::jobCounts($runId);
                $this->log($runId, 'info', self::PHASE_SERVICES, 'All services processed', $counts);

                // nothing is switched while a service failed: the switch would move its product to the new module with
                // the service's order still on the legacy one. A database hiccup (lock wait, lost connection) passes on
                // a second try; what fails again stops the run, which rolls itself back.
                if($counts[Store::JOB_FAILED] > 0)
                {
                    if(empty($run['summary']['retried']))
                    {
                        $n = Capsule::table(Store::jobsTable())->where('migration_id', $runId)->where('kind', Store::KIND_SERVICE)->where('status', Store::JOB_FAILED)
                            ->update(['status' => Store::JOB_PENDING, 'updated_at' => date('Y-m-d H:i:s')]);
                        Store::mergeSummary($runId, ['retried' => ['services' => $n, 'at' => date('Y-m-d H:i:s')]]);
                        $this->log($runId, 'warning', self::PHASE_SERVICES, $n . ' failed services are tried once more');

                        return $this->status($runId);
                    }
                    $written = (int)$counts[Store::JOB_DONE] + (int)$counts[Store::JOB_FAILED];
                    $this->autoRollback($runId, $counts[Store::JOB_FAILED] . ' of ' . $written . ' services could not be migrated, also on a second try');

                    return $this->status($runId);
                }
                Store::updateRun($runId, ['status' => Store::RUN_SWITCHING]);
            }

            return $this->status($runId);
        }

        if($run['status'] === Store::RUN_RUNNING)
        {
            Store::updateRun($runId, ['status' => Store::RUN_SWITCHING]);
        }
        $this->switchPhase($runId, $writer, $products);

        return $this->status($runId);
    }

    /** tblconfiguration rows the run changes: backed up first, restored by the rollback */
    const HOOK_LISTS = ['ActiveAddonModules', 'AddonModulesHooks', 'ModuleHooks'];

    /**
     * WHMCS loads the hooks of the addons in AddonModulesHooks and of the server modules in ModuleHooks only. The legacy
     * module is taken out of both for the run (its invoice, cron and cancellation hooks would act on services half
     * way to the new module). Returns the values before, for the switch to put back.
     */
    protected function pauseLegacyHooks(): array
    {
        $before = [];
        foreach(['AddonModulesHooks' => $this->reader->addonName(), 'ModuleHooks' => $this->reader->serverType()] as $setting => $module)
        {
            $value = Capsule::table('tblconfiguration')->where('setting', $setting)->value('value');
            if($value === null)
            {
                continue;
            }
            $before[$setting] = (string)$value;
            $list = array_filter(array_map('trim', explode(',', (string)$value)), function($name) use ($module) {
                return $name !== '' && strcasecmp($name, $module) !== 0;
            });
            Capsule::table('tblconfiguration')->where('setting', $setting)->update(['value' => implode(',', $list)]);
        }

        return $before;
    }

    /** The hook lists as they were before the run (services the run left on the legacy module keep its hooks) */
    protected static function restoreHookLists(int $runId): void
    {
        $run = Store::getRun($runId);
        foreach((array)($run['summary']['hook_lists'] ?? []) as $setting => $value)
        {
            Capsule::table('tblconfiguration')->where('setting', $setting)->update(['value' => (string)$value]);
        }
    }

    /** [table, column] looked up per service by the readers and the switch; indexed for the run when they have none */
    const HELPER_INDEXES = [
        ['tblsslorders', 'serviceid'], ['tblinvoiceitems', 'relid'],
        ['mod_sslstore_orders_data', 'service_id'], ['mod_autoinstallssl_orders', 'serviceid'],
        ['SSLCENTER_orders', 'service_id'], ['SSLCENTER_cron_queue', 'service_id'],
    ];
    const HELPER_INDEX_NAME = 'tssggs_migration_lookup';

    /** Returns the [table, name] pairs added (DDL - outside any transaction) */
    protected function addHelperIndexes(): array
    {
        $added = [];
        foreach(self::HELPER_INDEXES as [$table, $column])
        {
            try
            {
                if(Schema::addIndex($table, $column, self::HELPER_INDEX_NAME))
                {
                    $added[] = [$table, self::HELPER_INDEX_NAME];
                }
            }
            catch(\Throwable $e)
            {
                // only slower without it
            }
        }
        // the unified module's own table: kept (App\Libs\SchemaIndexes adds the same on every install)
        try
        {
            Schema::addIndex(Writer::tRequests(), 'serviceid', 'serviceid');
        }
        catch(\Throwable $e)
        {
        }

        return $added;
    }

    protected static function dropHelperIndexes(int $runId): void
    {
        $run = Store::getRun($runId);
        foreach((array)($run['summary']['helper_indexes'] ?? []) as $pair)
        {
            try
            {
                Schema::dropIndex((string)$pair[0], (string)$pair[1]);
            }
            catch(\Throwable $e)
            {
                Store::log($runId, 'warning', 'cleanup', 'Lookup index not dropped: ' . $e->getMessage(), ['table' => $pair[0]]);
            }
        }
    }

    /** @var array [run id, [pid => product name]] of the products kept as a record */
    protected $recordProducts = [0, []];

    protected function recordProductsOf(int $runId): array
    {
        if($this->recordProducts[0] !== $runId)
        {
            $list = [];
            foreach(Store::jobsByStatus($runId, Store::KIND_PRODUCT, Store::JOB_DONE) as $job)
            {
                if(!empty($job['data']['record']))
                {
                    $list[(int)$job['object_id']] = (string)($job['data']['name'] ?? '');
                }
            }
            $this->recordProducts = [$runId, $list];
        }

        return $this->recordProducts[1];
    }

    /** @var int run whose legacy product values were handed to the reader */
    protected $legacyProductsOfRun = 0;

    /**
     * A run either completes or is undone: a failure rolls it back at once (the legacy module carries on as before)
     * and the reason stays with the run for the error report. Returns true when the rollback went through.
     */
    protected function autoRollback(int $runId, string $reason): bool
    {
        $failed = [];
        foreach(Store::jobsByStatus($runId, Store::KIND_SERVICE, Store::JOB_FAILED) as $job)
        {
            $failed[] = ['service_id' => (int)$job['object_id'], 'reason' => (string)$job['reason']];
        }
        Store::mergeSummary($runId, ['auto_rollback' => ['reason' => $reason, 'failed_total' => count($failed), 'failed' => array_slice($failed, 0, self::SUMMARY_EXCEPTIONS), 'at' => date('Y-m-d H:i:s')]]);
        $this->log($runId, 'error', self::PHASE_ROLLBACK, 'Migration stopped: ' . $reason . '. Rolling back automatically.');

        $run = Store::getRun($runId);
        try
        {
            $backup = $run['backup_prefix'] ? new Backup($runId, $run['backup_prefix']) : null;
            if(!$backup || !$backup->tables())
            {
                Store::updateRun($runId, ['status' => Store::RUN_ROLLED_BACK, 'finished_at' => date('Y-m-d H:i:s'), 'last_error' => $reason . '. Nothing had been changed yet.']);

                return true;
            }
            self::restore($runId, $run, $backup, 'automatic rollback');
            Store::updateRun($runId, ['last_error' => $reason . '. The migration was rolled back automatically: the legacy module works as before. Download the error report and send it to support.']);

            return true;
        }
        catch(\Throwable $e)
        {
            Store::updateRun($runId, ['status' => Store::RUN_FAILED, 'last_error' => $reason . '. The automatic rollback failed too (' . $e->getMessage() . '): roll the migration back from this page.']);
            $this->log($runId, 'error', self::PHASE_ROLLBACK, 'Automatic rollback failed: ' . $e->getMessage(), ['trace' => substr($e->getTraceAsString(), 0, 2000)]);

            return false;
        }
    }

    /**
     * One runner at a time (two admin tabs, or a tab and the command line, must not write the same services twice).
     * The lock belongs to the database connection: it ends with the request even when PHP stops unexpectedly.
     */
    protected static function lock(): bool
    {
        $row = Capsule::select('SELECT GET_LOCK(?, 0) AS l', [self::lockName()]);

        return !empty($row) && (int)$row[0]->l === 1;
    }

    protected static function unlock(): void
    {
        Capsule::select('SELECT RELEASE_LOCK(?)', [self::lockName()]);
    }

    protected static function lockName(): string
    {
        // lock names are shared by all databases of the server
        return 'tssggs_migration_' . substr(md5((string)Capsule::connection()->getDatabaseName()), 0, 12);
    }

    protected function migrateService(int $runId, int $jobId, int $serviceId, Writer $writer, array $migratedProducts, bool $fetchKeys): void
    {
        try
        {
            if($this->legacyProductsOfRun !== $runId)
            {
                $legacy = [];
                foreach(Store::jobsByStatus($runId, Store::KIND_PRODUCT, Store::JOB_DONE) as $productJob)
                {
                    $legacy[(int)$productJob['object_id']] = (array)($productJob['data']['old_configoptions'] ?? []);
                }
                $this->reader->setLegacyProductOptions($legacy);
                $this->legacyProductsOfRun = $runId;
            }
            $rec = $this->reader->readService($serviceId);

            // a product without a gateway equivalent, kept as a record: whatever the service is, its details are kept
            $records = $this->recordProductsOf($runId);
            $code    = strtolower(trim((string)($rec['product_code'] ?? '')));
            $pid     = (int)($rec['product_id'] ?? 0);
            if(isset($records[$pid]) || ($code !== '' && in_array($pid, $migratedProducts, true) && $this->mapper->isRecord($code)))
            {
                $name    = $records[$pid] ?? (string)Capsule::table('tblproducts')->where('id', $pid)->value('name');
                $written = $writer->writeRecordService($rec, $name);
                $written += ['kind' => 'record', 'record' => true, 'legacy_status' => $rec['legacy_status'] ?? '', 'legacy_remoteid' => '', 'target_status' => '', 'warnings' => []];
                Store::finishJob($jobId, Store::JOB_DONE, null, $written);
                $this->log($runId, 'info', self::PHASE_SERVICES, 'Kept as a record (no gateway product)', ['remote_id' => $rec['remote_id'] ?? '', 'code' => $code], $serviceId);

                return;
            }

            if(!empty($rec['skip_reason']) || ($rec['kind'] ?? '') === 'unsupported')
            {
                Store::finishJob($jobId, Store::JOB_SKIPPED, $rec['skip_reason'] ?? 'Unsupported', ['kind' => $rec['kind'] ?? '', 'legacy_status' => $rec['legacy_status'] ?? '']);
                $this->log($runId, 'info', self::PHASE_SERVICES, 'Skipped: ' . ($rec['skip_reason'] ?? 'unsupported'), ['kind' => $rec['kind'] ?? ''], $serviceId);

                return;
            }
            if(!in_array((int)$rec['product_id'], $migratedProducts, true))
            {
                Store::finishJob($jobId, Store::JOB_SKIPPED, 'Product not migrated (no gateway mapping)', ['product_id' => $rec['product_id']]);
                $this->log($runId, 'warning', self::PHASE_SERVICES, 'Skipped: product not migrated', ['product_id' => $rec['product_id']], $serviceId);

                return;
            }
            if(!empty($rec['order_payload']) && (string)($rec['order_payload']['product']['id'] ?? '') === '')
            {
                throw new \RuntimeException('Order payload has no gateway product id');
            }

            $privateKey = (string)($rec['private_key'] ?? '');
            $keyNote    = '';
            if(!empty($rec['autoinstall']) && $privateKey === '' && $fetchKeys && !empty($rec['autoinstall']['legacy_pvtkey_id']))
            {
                [$privateKey, $keyNote] = $this->fetchCpanelKey($rec);
            }

            $written = $writer->writeService($rec, $privateKey);
            $written += [
                'kind'            => $rec['kind'], 'legacy_status' => $rec['legacy_status'] ?? '', 'legacy_remoteid' => '',
                'target_status'   => $rec['target_status'] ?? '', 'preordered' => !empty($rec['preordered']),
                'autoinstall'     => !empty($rec['autoinstall']), 'key' => !empty($rec['autoinstall']) ? ($privateKey !== '' ? 'present' : 'missing') : '',
                'warnings'        => $rec['warnings'] ?? [],
            ];
            Store::finishJob($jobId, Store::JOB_DONE, null, $written);
            $this->log($runId, 'info', self::PHASE_SERVICES, 'Migrated (' . $rec['kind'] . ', ' . ($rec['target_status'] ?? '') . ')' . ($keyNote ? '; ' . $keyNote : ''),
                ['remote_id' => $rec['remote_id'] ?? '', 'requests' => $written['requests'], 'warnings' => $rec['warnings'] ?? []], $serviceId);
        }
        catch(\Throwable $e)
        {
            Store::finishJob($jobId, Store::JOB_FAILED, $e->getMessage());
            $this->log($runId, 'error', self::PHASE_SERVICES, 'Failed: ' . $e->getMessage(), ['trace' => substr($e->getTraceAsString(), 0, 2000)], $serviceId);
        }
    }

    /**
     * TSS keeps AutoInstall keys in the cPanel account. Returns [pem, note].
     */
    protected function fetchCpanelKey(array $rec): array
    {
        $state = $rec['autoinstall'];
        try
        {
            $service = Capsule::table('tblhosting')
                ->select('tblhosting.id', 'tblhosting.domain', 'tblhosting.username as user', 'tblproducts.name', 'tblservers.hostname', 'tblservers.ipaddress',
                    'tblservers.secure', 'tblservers.port', 'tblservers.accesshash as hash', 'tblservers.username', 'tblservers.password', 'tblhosting.packageid')
                ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
                ->join('tblservers', 'tblhosting.server', '=', 'tblservers.id')
                ->where('tblhosting.id', (int)$state['cpanel_service_id'])->first();
            if(!$service)
            {
                return ['', 'cPanel account not found for key fetch'];
            }
            $cpanel = new Cpanel();
            $cpanel->setService($service);
            $pem = $cpanel->getPrivateKey($service->user, $state['legacy_pvtkey_id'], (string)($rec['order_payload']['csr'] ?? ''));

            return [$pem, 'private key fetched from cPanel'];
        }
        catch(\Throwable $e)
        {
            return ['', 'private key not fetched from cPanel (' . $e->getMessage() . '); re-key at next reissue'];
        }
    }

    // =================================================================== switch

    protected function switchPhase(int $runId, Writer $writer, array $products): void
    {
        $run = Store::getRun($runId);
        try
        {
            // services configured by clients while the batches ran: re-read and rewrite
            $changed = 0;
            foreach(Store::jobsByStatus($runId, Store::KIND_SERVICE, Store::JOB_DONE) as $job)
            {
                if(($job['data']['target_status'] ?? '') !== 'Awaiting Configuration')
                {
                    continue;
                }
                $current = Capsule::table('tblsslorders')->where('id', (int)$job['ssl_order_id'])->value('status');
                if($current && $current !== 'Awaiting Configuration')
                {
                    $writer->undoService($job['data']);
                    Capsule::table(Store::jobsTable())->where('id', $job['id'])->update(['status' => Store::JOB_PENDING, 'updated_at' => date('Y-m-d H:i:s')]);
                    $this->migrateService($runId, $job['id'], (int)$job['object_id'], $writer, $products, (bool)$run['options']['fetch_cpanel_keys']);
                    $changed++;
                }
            }

            // orders created after the snapshot (new sales during the run)
            $maxId    = (int)($run['summary']['snapshot_max_order_id'] ?? 0);
            $newRows  = Capsule::table('tblsslorders')->whereIn('module', $this->reader->orderModules())->where('id', '>', $maxId)->get();
            $rescan   = 0;
            foreach($newRows as $row)
            {
                if(!Store::hasJob($runId, Store::KIND_SERVICE, (int)$row->serviceid))
                {
                    // not in the backup yet (created after it): a rollback must be able to put the row back
                    (new Backup($runId, $run['backup_prefix']))->appendRows('tblsslorders', [(int)$row->id]);
                    $jobId = Store::addJob($runId, Store::KIND_SERVICE, (int)$row->serviceid);
                    $this->migrateService($runId, $jobId, (int)$row->serviceid, $writer, $products, (bool)$run['options']['fetch_cpanel_keys']);
                    $rescan++;
                }
            }
            if($changed || $rescan)
            {
                $this->log($runId, 'info', self::PHASE_SWITCH, 'Rescan done', ['reconfigured' => $changed, 'new_orders' => $rescan]);
            }
            $failedNow = (int)Store::jobCounts($runId)[Store::JOB_FAILED];
            if($failedNow > 0)
            {
                $this->autoRollback($runId, $failedNow . ' services changed or ordered during the run could not be migrated');

                return;
            }

            $frozen = $this->reader->freeze();
            if($frozen)
            {
                $this->log($runId, 'info', self::PHASE_SWITCH, 'Legacy automation frozen', ['changes' => $frozen]);
            }

            // the switch itself: one transaction
            $doneOrderIds = [];
            foreach(Store::jobsByStatus($runId, Store::KIND_SERVICE, Store::JOB_DONE) as $job)
            {
                if(!empty($job['ssl_order_id']))
                {
                    $doneOrderIds[] = (int)$job['ssl_order_id'];
                }
            }
            Capsule::connection()->transaction(function() use ($writer, $products, $doneOrderIds) {
                $writer->switchProducts($products);
                $writer->switchOrders($doneOrderIds);
            });
            Store::mergeSummary($runId, ['snapshot_max_order_id' => (int)Capsule::table('tblsslorders')->max('id')]);
            $this->log($runId, 'info', self::PHASE_SWITCH, 'Switched to the unified module', ['products' => count($products), 'orders' => count($doneOrderIds)]);

            // the legacy hooks come back for what stays on the legacy module; the deactivation below takes the addon's out
            self::restoreHookLists($runId);

            // legacy addon off (its tables stay; rollback reactivates it)
            $deactivated = null;
            if(!empty($run['options']['deactivate_legacy']))
            {
                $deactivated = LegacyModuleControl::deactivate($this->reader->addonName());
                $this->log($runId, $deactivated['ok'] ? 'info' : 'warning', self::PHASE_SWITCH, 'Legacy addon deactivation: ' . ($deactivated['ok'] ? 'done' : 'FAILED') . ' (' . $deactivated['method'] . ')', $deactivated);
            }

            $verify = $this->verify($runId);
            Store::mergeSummary($runId, [
                'jobs'               => Store::jobCounts($runId),
                'verify'             => $verify,
                'legacy_deactivated' => $deactivated,
                'cron_lines'         => $this->reader->cronLines(),
                'exceptions'         => $this->exceptions($runId),
            ]);
            Store::updateRun($runId, ['status' => Store::RUN_COMPLETED, 'finished_at' => date('Y-m-d H:i:s'), 'last_error' => $verify['ok'] ? null : implode('; ', $verify['problems'])]);
            $this->log($runId, $verify['ok'] ? 'info' : 'warning', self::PHASE_SWITCH, 'Migration completed' . ($verify['ok'] ? '' : ' with verification warnings'), $verify);

            self::dropHelperIndexes($runId);

            // orders, renewals and upgrades paid while the legacy module was paused: now on the unified module
            $held = LegacyBlock::replay($runId);
            if($held['done'] || $held['failed'])
            {
                Store::mergeSummary($runId, ['held_commands' => $held]);
            }
        }
        catch(\Throwable $e)
        {
            Store::updateRun($runId, ['status' => Store::RUN_FAILED, 'last_error' => $e->getMessage()]);
            $this->log($runId, 'error', self::PHASE_SWITCH, 'Switch failed: ' . $e->getMessage(), ['trace' => substr($e->getTraceAsString(), 0, 2000)]);
            $this->autoRollback($runId, 'The switch failed: ' . $e->getMessage());
        }
    }

    protected function verify(int $runId): array
    {
        $problems = [];
        $done     = Store::jobsByStatus($runId, Store::KIND_SERVICE, Store::JOB_DONE);
        $active   = 0;
        foreach($done as $job)
        {
            $order = Capsule::table('tblsslorders')->where('id', (int)$job['ssl_order_id'])->first();
            if(!$order)
            {
                if(!empty($job['data']['record']))
                {
                    continue; // a record of a service that had no SSL order (unpaid)
                }
                $problems[] = 'service ' . $job['object_id'] . ': SSL order row missing';
                continue;
            }
            if($order->module !== Writer::MODULE)
            {
                $problems[] = 'service ' . $job['object_id'] . ': module not switched';
            }
            if($order->status === 'Configuration Submitted' && ($job['data']['kind'] ?? '') === 'ssl')
            {
                $active++;
                foreach(['addOrder', 'certificate'] as $name)
                {
                    $n = (int)Capsule::table(Writer::tRequests())->where('serviceid', (int)$job['object_id'])->where('name', $name)->count();
                    if($n !== 1)
                    {
                        $problems[] = 'service ' . $job['object_id'] . ': ' . $n . ' ' . $name . ' rows (expected 1)';
                    }
                }
            }
        }
        $legacyLeft = (int)Capsule::table('tblsslorders')->whereIn('module', $this->reader->orderModules())->count();

        return ['ok' => !$problems, 'checked' => count($done), 'active_ssl' => $active, 'legacy_rows_left' => $legacyLeft, 'problems' => array_slice($problems, 0, 50)];
    }

    protected function exceptions(int $runId): array
    {
        $list = [];
        foreach([Store::JOB_FAILED, Store::JOB_SKIPPED] as $status)
        {
            foreach(Store::jobsByStatus($runId, Store::KIND_SERVICE, $status) as $job)
            {
                $list[] = ['service_id' => (int)$job['object_id'], 'status' => $status, 'reason' => $job['reason']];
            }
        }
        foreach(Store::jobsByStatus($runId, Store::KIND_PRODUCT, Store::JOB_SKIPPED) as $job)
        {
            $list[] = ['product_id' => (int)$job['object_id'], 'status' => Store::JOB_SKIPPED, 'reason' => $job['reason']];
        }

        // the summary is one text column: the full list stays in the jobs table and the log
        return array_slice($list, 0, self::SUMMARY_EXCEPTIONS);
    }

    // =================================================================== status / rollback / cleanup

    public function status(int $runId): array
    {
        $run    = Store::getRun($runId);
        $counts = Store::jobCounts($runId);
        $stale  = in_array($run['status'], [Store::RUN_PREPARING, Store::RUN_RUNNING, Store::RUN_SWITCHING], true)
            && $run['heartbeat_at'] && (time() - strtotime($run['heartbeat_at'])) > self::STALE_AFTER_SEC;

        return [
            'run'      => $run,
            'jobs'     => $counts,
            'percent'  => $counts['total'] ? (int)round((($counts['total'] - $counts['pending']) / $counts['total']) * 100) : 0,
            'stale'    => $stale,
            'finished' => in_array($run['status'], [Store::RUN_COMPLETED, Store::RUN_FAILED, Store::RUN_ROLLED_BACK], true),
        ];
    }

    /**
     * Restores everything this run changed. Possible until the backups are deleted.
     */
    public static function rollback(int $runId, string $by = 'admin'): array
    {
        $run = Store::getRun($runId);
        if(!$run || !$run['backup_prefix'])
        {
            throw new \RuntimeException('No backups for migration #' . $runId);
        }
        if($run['status'] === Store::RUN_ROLLED_BACK)
        {
            // a second rollback would put the old copy over the data the legacy module changed since
            throw new \RuntimeException('Migration #' . $runId . ' is already rolled back');
        }
        $backup = new Backup($runId, $run['backup_prefix']);
        if(!$backup->tables())
        {
            throw new \RuntimeException('Backup tables of migration #' . $runId . ' no longer exist');
        }
        if(!self::lock())
        {
            throw new \RuntimeException('Migration #' . $runId . ' is being processed. Try again in a moment.');
        }
        try
        {
            return self::restore($runId, $run, $backup, $by);
        }
        finally
        {
            self::unlock();
        }
    }

    protected static function restore(int $runId, array $run, Backup $backup, string $by): array
    {
        $source = $run['source'];
        $reader = Detector::reader($source);
        $writer = new Writer($runId, $source);
        Store::log($runId, 'info', self::PHASE_ROLLBACK, 'Rollback started by ' . $by);

        // legacy tables that were dropped since are created before the transaction (creating a table would end it)
        foreach((array)constant(get_class($reader) . '::TABLES') as $table)
        {
            $backup->createMissing($table);
        }

        Capsule::connection()->transaction(function() use ($runId, $run, $backup, $reader, $writer) {
            foreach(Store::jobsByStatus($runId, Store::KIND_SERVICE, Store::JOB_DONE) as $job)
            {
                $writer->undoService($job['data']);
            }
            // markup rules created by the run (settings and product jobs)
            $markupIds = [];
            foreach(array_merge(Store::jobsByStatus($runId, Store::KIND_SETTINGS, Store::JOB_DONE), Store::jobsByStatus($runId, Store::KIND_PRODUCT, Store::JOB_DONE)) as $job)
            {
                // runs started before 1.3.0 stored the same ids under their old name
                    $markupIds = array_merge($markupIds, (array)($job['data']['markup_ids'] ?? $job['data']['commission_ids'] ?? []));
            }
            if($markupIds)
            {
                Capsule::table(Markups::table())->whereIn('id', $markupIds)->delete();
            }
            // e-mail template rows created by the copy; the ones it overwrote come back from the row backup
            $templateIds = [];
            foreach(Store::jobsByStatus($runId, Store::KIND_SETTINGS, Store::JOB_DONE) as $job)
            {
                $templateIds = array_merge($templateIds, (array)($job['data']['template_ids'] ?? []));
            }
            if($templateIds)
            {
                Capsule::table('tblemailtemplates')->whereIn('id', $templateIds)->delete();
            }
            // configurable options the run created (code signing "Certificate Delivery"): they are not in the backup
            $removed = $writer->removeCreatedOptions(array_map('intval', $run['summary']['products'] ?? []), $backup);
            if($removed)
            {
                Store::log($runId, 'info', self::PHASE_ROLLBACK, 'Configurable options created by the migration removed', ['options' => $removed]);
            }
            // unified rows written for products are reverted through the row backups below
            $backup->removeAddedPricing();
            $backup->restoreRows('tblpricing');
            $backup->restoreRows('tblsslorders');
            $backup->restoreRows('tblproducts');
            $backup->restoreRows('tblproductconfiggroups');
            $backup->restoreRows('tblproductconfigoptions');
            $backup->restoreRows('tblproductconfigoptionssub');
            // settings the run added (the merge of the legacy settings into a fresh install) go, the rest comes back
            $backup->removeAddedRows('tbladdonmodules', 'module', ['configuration_' . Writer::MODULE, $reader->addonName()]);
            $backup->restoreRows('tbladdonmodules');
            $backup->restoreRows('tblconfiguration');
            $backup->restoreRows('tblemailtemplates');
            // every legacy table this run copied - also one that was dropped since (it is created again)
            $restored = [];
            foreach(array_merge((array)constant(get_class($reader) . '::TABLES'), $reader->tables()) as $table)
            {
                if(!isset($restored[$table]))
                {
                    $backup->restoreTable($table);
                    $restored[$table] = true;
                }
            }
        });

        $reactivated = LegacyModuleControl::activate($reader->addonName());
        self::dropHelperIndexes($runId);
        Store::updateRun($runId, ['status' => Store::RUN_ROLLED_BACK, 'finished_at' => date('Y-m-d H:i:s')]);
        Store::log($runId, 'info', self::PHASE_ROLLBACK, 'Rollback completed', ['legacy_reactivated' => $reactivated]);

        // commands held while the legacy module was paused and already run on the new module after the switch: the
        // rollback does not undo them (they are ordinary module actions) - listed for a check on the legacy module
        foreach(Store::jobsByStatus($runId, LegacyBlock::KIND_HELD, Store::JOB_DONE) as $job)
        {
            Store::log($runId, 'warning', self::PHASE_ROLLBACK, $job['reason'] . ' ran on the new module after the switch and is not rolled back: check this service on the legacy module', [], (int)$job['object_id']);
        }

        // commands still held: back on the legacy module, they run there
        $held = LegacyBlock::replay($runId);

        return ['ok' => true, 'legacy_reactivated' => $reactivated, 'held_commands' => $held];
    }

    /**
     * Drops the backup tables of a run (manually or when backup_expires_at has passed)
     */
    public static function deleteBackups(int $runId, string $by = 'admin'): int
    {
        $run = Store::getRun($runId);
        if(!$run || !$run['backup_prefix'])
        {
            return 0;
        }
        $n = (new Backup($runId, $run['backup_prefix']))->drop();
        Store::updateRun($runId, ['backup_prefix' => null, 'backup_expires_at' => null]);
        Store::log($runId, 'info', 'cleanup', 'Backup tables deleted by ' . $by, ['tables' => $n]);

        return $n;
    }

    public static function extendBackups(int $runId, int $days = Store::BACKUP_RETENTION_DAYS): void
    {
        Store::updateRun($runId, ['backup_expires_at' => date('Y-m-d H:i:s', strtotime('+' . $days . ' days'))]);
        Store::log($runId, 'info', 'cleanup', 'Backup retention extended by ' . $days . ' days');
    }

    /**
     * Housekeeping for the cron: expired backups and old log rows
     */
    public static function housekeeping(): array
    {
        Store::createTables();
        $dropped = 0;
        foreach(Capsule::table(Store::runsTable())->whereNotNull('backup_prefix')->where('backup_expires_at', '<', date('Y-m-d H:i:s'))
            // the backups of a run that failed or stopped are the only copy of the original data: never dropped automatically
            ->whereIn('status', [Store::RUN_COMPLETED, Store::RUN_ROLLED_BACK])->get() as $run)
        {
            $dropped += self::deleteBackups((int)$run->id, 'cron');
        }

        return ['backups_dropped' => $dropped, 'log_rows_purged' => Store::purgeOldLogs()];
    }

    public static function log(int $runId, string $level, string $phase, string $message, array $context = [], int $serviceId = 0): void
    {
        Store::log($runId, $level, $phase, $message, $context, $serviceId);
    }

    /**
     * Error report of a run, for the administrator to send to support: versions, what happened, the services that
     * failed and the full log. E-mail addresses are masked.
     */
    public static function errorReport(int $runId): string
    {
        $run = Store::getRun($runId);
        if(!$run)
        {
            return 'Migration #' . $runId . ' not found';
        }
        $whmcs   = (string)Capsule::table('tblconfiguration')->where('setting', 'Version')->value('value');
        $mysql   = Capsule::selectOne('SELECT VERSION() AS v');
        $summary = $run['summary'] ?? [];
        $lines   = [
            \ModulesGarden\TSSGGSModule\App\Libs\Edition::name() . ' - migration error report',
            str_repeat('=', 60),
            'Run:            #' . $runId . ' (' . strtoupper((string)$run['source']) . ', legacy module ' . ($run['source_version'] ?: '?') . ')',
            'Status:         ' . $run['status'],
            'Started:        ' . ($run['started_at'] ?: $run['created_at']) . ' by ' . $run['started_by'],
            'Finished:       ' . ($run['finished_at'] ?: '-'),
            'Module version: ' . self::moduleVersion(),
            'WHMCS:          ' . $whmcs . ', PHP ' . PHP_VERSION . ', MySQL ' . ($mysql ? (string)$mysql->v : '?'),
            'Result:         ' . (string)$run['last_error'],
            'Services:       ' . json_encode(Store::jobCounts($runId)),
            '',
        ];
        $failed = Store::jobsByStatus($runId, Store::KIND_SERVICE, Store::JOB_FAILED);
        if(!$failed && !empty($summary['auto_rollback']['failed']))
        {
            $failed = array_map(function($f) { return ['object_id' => $f['service_id'], 'reason' => $f['reason']]; }, $summary['auto_rollback']['failed']);
        }
        if($failed)
        {
            $lines[] = 'Services that could not be migrated (' . count($failed) . '):';
            foreach(array_slice($failed, 0, 1000) as $job)
            {
                $pid     = (int)Capsule::table('tblhosting')->where('id', (int)$job['object_id'])->value('packageid');
                $lines[] = '  service #' . (int)$job['object_id'] . ' (product #' . $pid . '): ' . (string)$job['reason'];
            }
            $lines[] = '';
        }
        $lines[] = 'Log:';
        foreach(Store::logRows($runId, 100000) as $row)
        {
            $context = (string)$row['context'];
            $lines[] = $row['created_at'] . ' ' . str_pad(strtoupper((string)$row['level']), 7) . ' ' . str_pad((string)$row['phase'], 9) . ' '
                . ($row['service_id'] ? '#' . $row['service_id'] . ' ' : '') . $row['message'] . ($context !== '' && $context !== '[]' ? ' ' . $context : '');
        }

        // e-mail addresses of clients and contacts are not needed to find a fault
        return preg_replace('/([A-Za-z0-9._%+-])[A-Za-z0-9._%+-]*@([A-Za-z0-9.-]+\.[A-Za-z]{2,})/', '$1***@$2', implode("\n", $lines) . "\n");
    }

    protected static function moduleVersion(): string
    {
        $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'configuration.yml';
        if(is_file($file) && preg_match("/version:\s*'?([0-9.]+)/", (string)file_get_contents($file), $m))
        {
            return $m[1];
        }

        return '?';
    }

    /**
     * Human-readable export of a run's log (CSV)
     */
    public static function logCsv(int $runId): string
    {
        $out = fopen('php://temp', 'w+');
        fputcsv($out, ['time', 'level', 'phase', 'service_id', 'message', 'context']);
        foreach(Store::logRows($runId, 100000) as $row)
        {
            fputcsv($out, [$row['created_at'], $row['level'], $row['phase'], $row['service_id'], $row['message'], (string)$row['context']]);
        }
        rewind($out);

        return stream_get_contents($out);
    }
}
