<?php

namespace ModulesGarden\TSSGGSModule\App\Migration;

use ModulesGarden\TSSGGSModule\App\Libs\Commissions;
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
        if(Store::activeRun())
        {
            throw new \RuntimeException('Another migration is still running. Resume or roll it back first.');
        }
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
            // 1. backups
            $backup  = new Backup($runId);
            $tables  = [];
            $skipLog = $options['backup_logs'] ? [] : ['SSLCENTER_logs', 'mod_sslstore_logs', 'mod_autoinstallssl_logs'];
            foreach($this->reader->tables() as $table)
            {
                if(in_array($table, $skipLog, true))
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
            $tables[]   = $backup->copyRows('tbladdonmodules', function($q) { $q->whereIn('module', ['configuration_' . Writer::MODULE, $this->reader->addonName()]); });
            $tables[]   = $backup->copyRows('tblconfiguration', function($q) { $q->where('setting', 'ActiveAddonModules'); });
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
                'summary'           => ['backups' => $backup->tables(), 'snapshot_max_order_id' => $maxOrderId, 'products' => [], 'settings_filled' => []],
            ]);
            $this->log($runId, 'info', self::PHASE_PREPARE, 'Backups written', ['tables' => $tables]);

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
            foreach($pids as $pid)
            {
                $p = $this->reader->readProduct($pid);
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
            Store::mergeSummary($runId, ['products' => $migratedPids, 'settings_filled' => $filled]);

            // per-client commissions of the migrated products (GGS)
            if(method_exists($this->reader, 'readCommissions'))
            {
                $rules = array_values(array_filter($this->reader->readCommissions(), function($rule) use ($migratedPids) {
                    return in_array((int)$rule['product_id'], $migratedPids, true);
                }));
                $ids = $writer->writeCommissions($rules);
                Store::addJob($runId, Store::KIND_SETTINGS, 0, 0, Store::JOB_DONE, null, ['commission_ids' => $ids]);
                $this->log($runId, 'info', self::PHASE_PREPARE, 'Client commissions copied', ['rules' => count($ids), 'legacy_rules' => count($rules)]);
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
            $this->log($runId, 'error', self::PHASE_PREPARE, 'Preparation failed: ' . $e->getMessage());
            throw $e;
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
                Store::updateRun($runId, ['status' => Store::RUN_SWITCHING]);
                $this->log($runId, 'info', self::PHASE_SERVICES, 'All services processed', Store::jobCounts($runId));
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

    protected function migrateService(int $runId, int $jobId, int $serviceId, Writer $writer, array $migratedProducts, bool $fetchKeys): void
    {
        try
        {
            $rec = $this->reader->readService($serviceId);

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
                    Capsule::table(Store::TABLE_JOBS)->where('id', $job['id'])->update(['status' => Store::JOB_PENDING, 'updated_at' => date('Y-m-d H:i:s')]);
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
                    $jobId = Store::addJob($runId, Store::KIND_SERVICE, (int)$row->serviceid);
                    $this->migrateService($runId, $jobId, (int)$row->serviceid, $writer, $products, (bool)$run['options']['fetch_cpanel_keys']);
                    $rescan++;
                }
            }
            if($changed || $rescan)
            {
                $this->log($runId, 'info', self::PHASE_SWITCH, 'Rescan done', ['reconfigured' => $changed, 'new_orders' => $rescan]);
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
            Store::updateRun($runId, ['status' => $verify['ok'] ? Store::RUN_COMPLETED : Store::RUN_COMPLETED, 'finished_at' => date('Y-m-d H:i:s'), 'last_error' => $verify['ok'] ? null : implode('; ', $verify['problems'])]);
            $this->log($runId, $verify['ok'] ? 'info' : 'warning', self::PHASE_SWITCH, 'Migration completed' . ($verify['ok'] ? '' : ' with verification warnings'), $verify);
        }
        catch(\Throwable $e)
        {
            Store::updateRun($runId, ['status' => Store::RUN_FAILED, 'last_error' => $e->getMessage()]);
            $this->log($runId, 'error', self::PHASE_SWITCH, 'Switch failed: ' . $e->getMessage(), ['trace' => substr($e->getTraceAsString(), 0, 2000)]);
            throw $e;
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
                    $n = (int)Capsule::table(Writer::T_REQUESTS)->where('serviceid', (int)$job['object_id'])->where('name', $name)->count();
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

        return $list;
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
        $backup = new Backup($runId, $run['backup_prefix']);
        if(!$backup->tables())
        {
            throw new \RuntimeException('Backup tables of migration #' . $runId . ' no longer exist');
        }
        $source = $run['source'];
        $reader = Detector::reader($source);
        $writer = new Writer($runId, $source);
        Store::log($runId, 'info', self::PHASE_ROLLBACK, 'Rollback started by ' . $by);

        Capsule::connection()->transaction(function() use ($runId, $backup, $reader, $writer) {
            foreach(Store::jobsByStatus($runId, Store::KIND_SERVICE, Store::JOB_DONE) as $job)
            {
                $writer->undoService($job['data']);
            }
            // commission rules created by the run (settings and product jobs)
            $commissionIds = [];
            foreach(array_merge(Store::jobsByStatus($runId, Store::KIND_SETTINGS, Store::JOB_DONE), Store::jobsByStatus($runId, Store::KIND_PRODUCT, Store::JOB_DONE)) as $job)
            {
                $commissionIds = array_merge($commissionIds, (array)($job['data']['commission_ids'] ?? []));
            }
            if($commissionIds)
            {
                Capsule::table(Commissions::TABLE)->whereIn('id', $commissionIds)->delete();
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
            // unified rows written for products are reverted through the row backups below
            $backup->restoreRows('tblsslorders');
            $backup->restoreRows('tblproducts');
            $backup->restoreRows('tblproductconfiggroups');
            $backup->restoreRows('tblproductconfigoptions');
            $backup->restoreRows('tblproductconfigoptionssub');
            $backup->restoreRows('tbladdonmodules');
            $backup->restoreRows('tblconfiguration');
            $backup->restoreRows('tblemailtemplates');
            foreach($reader->tables() as $table)
            {
                $backup->restoreTable($table);
            }
        });

        $reactivated = LegacyModuleControl::activate($reader->addonName());
        Store::updateRun($runId, ['status' => Store::RUN_ROLLED_BACK, 'finished_at' => date('Y-m-d H:i:s')]);
        Store::log($runId, 'info', self::PHASE_ROLLBACK, 'Rollback completed', ['legacy_reactivated' => $reactivated]);

        return ['ok' => true, 'legacy_reactivated' => $reactivated];
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
        foreach(Capsule::table(Store::TABLE_RUNS)->whereNotNull('backup_prefix')->where('backup_expires_at', '<', date('Y-m-d H:i:s'))->get() as $run)
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
