<?php

namespace TSSGGSModule\App\Libs\Setup;

use TSSGGSModule\App\Kernel\Lang;
use TSSGGSModule\App\Kernel\Page;
use TSSGGSModule\App\Libs\Support\Edition;
use TSSGGSModule\App\Migration\Choice;
use TSSGGSModule\App\Migration\Credentials;
use TSSGGSModule\App\Migration\Detector;
use TSSGGSModule\App\Migration\Engine;
use TSSGGSModule\App\Migration\Store;
use WHMCS\Database\Capsule;

/**
 * What the Migration page shows (templates admin/migration): the legacy modules found with their numbers and the
 * start form, and the runs with their progress, result, backups and rollback. The setup wizard's migration step
 * shows the same blocks for this edition's legacy module only (wizard mode).
 *
 * A block is ['kind' => 'run' | 'source' | 'none' | 'done' | 'later', ...]; texts are of the group admin.migration.
 */
class MigrationScreen
{
    const TEXTS = 'admin.migration.';

    /** session entry with the message of an action that failed */
    const SESSION_ERROR = 'tssggs_migration_error';

    protected $lang;
    protected $wizard;

    public function __construct(Lang $lang, bool $wizard = false)
    {
        $this->lang   = $lang;
        $this->wizard = $wizard;
    }

    /** @return array error (of the last action), blocks, wizard */
    public function data(): array
    {
        $error = (string)($_SESSION[self::SESSION_ERROR] ?? '');
        unset($_SESSION[self::SESSION_ERROR]);

        return ['error' => $error, 'blocks' => $this->wizard ? $this->wizardBlocks() : $this->pageBlocks(), 'wizard' => $this->wizard];
    }

    protected function pageBlocks(): array
    {
        $blocks = [];
        $active = Store::activeRun();
        if($active)
        {
            $blocks[] = $this->run($active, true);
        }

        $found = Detector::detect();
        if(!$found && !$active)
        {
            $blocks[] = ['kind' => 'none'];
        }
        foreach($found as $source => $info)
        {
            if(!$active || $active['source'] !== $source)
            {
                $blocks[] = $this->source($source, $info);
            }
        }

        Store::createTables();
        foreach(Capsule::table(Store::runsTable())->where('status', '<>', Store::RUN_DRY_RUN)->orderBy('id', 'desc')->limit(5)->get() as $row)
        {
            if(!$active || (int)$active['id'] !== (int)$row->id)
            {
                $blocks[] = $this->run((array)Store::getRun((int)$row->id), false);
            }
        }

        return $blocks;
    }

    /**
     * Wizard step: the run in progress, or a completed run and the way on, or the legacy module to start with
     * (after a run that was rolled back too, with its report). "Later" goes on without migrating.
     */
    protected function wizardBlocks(): array
    {
        $source = Edition::get();
        $active = Store::activeRun();
        if($active)
        {
            return [$this->run($active, true)];
        }

        Store::createTables();
        $row    = Capsule::table(Store::runsTable())->where('source', $source)->where('status', '<>', Store::RUN_DRY_RUN)->orderBy('id', 'desc')->first();
        $latest = $row ? Store::getRun((int)$row->id) : null;
        $next   = Page::pageUrl('configuration', 'step6');
        if($latest && $latest['status'] === Store::RUN_COMPLETED)
        {
            return [$this->run($latest, false), ['kind' => 'done', 'next' => $next]];
        }

        $blocks = $latest ? [$this->run($latest, false)] : [];
        $found  = Detector::detect();
        if(isset($found[$source]))
        {
            $blocks[] = $this->source($source, $found[$source]);
        }
        $blocks[] = ['kind' => 'later', 'next' => $next];

        return $blocks;
    }

    /* ---- one legacy module ---- */

    protected function source(string $source, array $info): array
    {
        $c      = $info['counts'];
        $choice = Choice::get($source);
        $band   = (string)$info['support']['band'];
        $block  = [
            'kind'   => 'source',
            'source' => $source,
            'title'  => $info['label'] . ($info['version'] ? ' ' . $info['version'] : ''),
            'cards'  => [
                ['label' => $this->t('card_products'), 'value' => (int)$c['products']['total'], 'sub' => $this->t('card_products_sub', ['hidden' => (int)$c['products']['hidden'], 'retired' => (int)$c['products']['retired']])],
                ['label' => $this->t('card_services'), 'value' => (int)($c['services']['Active'] ?? 0), 'sub' => self::counts((array)$c['services'])],
                ['label' => $this->t('card_orders'), 'value' => (int)array_sum((array)$c['orders']), 'sub' => self::counts((array)$c['orders'])],
                ['label' => $this->t('card_subscriptions'), 'value' => (int)$c['acme'] + (int)$c['ais'], 'sub' => 'ACME ' . (int)$c['acme'] . ' · AIS ' . (int)$c['ais']],
                ['label' => $this->t('card_autoinstall'), 'value' => (int)$c['autoinstall'], 'sub' => $this->t('card_queue_open', ['n' => (int)$c['queue_open']])],
                ['label' => $this->t('card_unconfigured'), 'value' => (int)$c['unconfigured'], 'sub' => $this->t('card_unconfigured_sub', ['pre' => (int)$c['unconfigured_preordered'], 'unpaid' => (int)$c['unpaid']])],
            ],
            'supportTone' => $band === 'full' ? 'ok' : ($band === 'partial' ? 'warn' : 'bad'),
            'supportText' => $this->t('support_' . $band),
            'supportMore' => trim((string)$info['support']['reason'] . (!$info['installed'] ? ' ' . $this->t('files_missing') : '')),
            'unsupported' => '',
            'others'      => '',
            'credentials' => $this->t('credentials_' . Credentials::state($source)),
            'choice'      => $choice ? $this->t('choice_' . $choice) : $this->t('choice_none'),
            // nothing | start | old (the legacy module's version is too old to migrate)
            'state'       => 'old',
            'toChoose'    => [],
            'quiet'       => '',
            'quietTitle'  => '',
            'mapTitle'    => '',
        ];

        $unsupported = array_filter((array)$c['unsupported']);
        if($unsupported)
        {
            $block['unsupported'] = $this->t('unsupported_found') . ' ' . implode(', ', array_map(function($k, $v) {
                return $v . ' ' . $k;
            }, array_keys($unsupported), $unsupported));
        }
        if($c['other_ssl_modules'])
        {
            $block['others'] = $this->t('other_modules') . ' ' . implode(', ', array_map(function($k, $v) {
                return $k . ' (' . $v . ')';
            }, array_keys($c['other_ssl_modules']), $c['other_ssl_modules']));
        }

        if((int)$c['products']['total'] === 0 && array_sum(array_map('intval', (array)$c['services'])) === 0)
        {
            // everything was moved by an earlier run
            $block['state'] = 'nothing';
        }
        elseif($info['support']['supported'])
        {
            $block['state'] = 'start';
            // products without a gateway match: the administrator chooses before the start (the form carries it)
            $engine    = new Engine($source);
            $unmatched = $engine->unmatched();
            $toChoose  = array_values(array_filter($unmatched, [Engine::class, 'needsChoice']));
            $quiet     = array_values(array_filter($unmatched, function($item) {
                return !Engine::needsChoice($item);
            }));
            if($toChoose)
            {
                $block['toChoose'] = $this->mapping($toChoose, $engine->alternatives());
                $block['mapTitle'] = $this->t('map_title', ['n' => count($toChoose)]);
            }
            if($quiet)
            {
                $block['quietTitle'] = $this->t('map_quiet', ['n' => count($quiet)]);
                $block['quiet']      = implode(', ', array_column($quiet, 'label'));
            }
        }

        return $block;
    }

    /**
     * One row per legacy product (or TSS order code) without a gateway match: an alternative gateway product, or keep
     * the certificates as records. Products of the same brand are listed first.
     */
    protected function mapping(array $unmatched, array $alternatives): array
    {
        $rows = [];
        foreach($unmatched as $item)
        {
            $s       = $item['services'];
            $brandOf = strtolower((string)preg_replace('/[^a-z].*$/i', '', implode(' ', $item['names']) ?: $item['ref']));
            $same    = $other = [];
            foreach($item['unsupported'] ? [] : $alternatives as $alt)
            {
                $option = ['id' => (string)$alt['id'], 'name' => $alt['name'] . ($alt['validation'] ? ' (' . strtoupper($alt['validation']) . ')' : '')];
                if($brandOf !== '' && stripos($alt['brand'] . ' ' . $alt['name'], $brandOf) !== false)
                {
                    $same[] = $option;
                }
                else
                {
                    $other[] = $option;
                }
            }
            $rows[] = [
                'ref'         => (string)$item['ref'],
                'label'       => (string)$item['label'],
                'unsupported' => (bool)$item['unsupported'],
                'services'    => $this->t('map_services', ['active' => (int)$s['active'], 'awaiting' => (int)$s['awaiting'], 'other' => (int)$s['other']]),
                'same'        => $same,
                'other'       => $other,
            ];
        }

        return $rows;
    }

    /* ---- one run ---- */

    protected function run(array $run, bool $isActive): array
    {
        $status  = (new Engine($run['source']))->status((int)$run['id']);
        $summary = (array)($run['summary'] ?? []);
        $id      = (int)$run['id'];
        $module  = \TSSGGSModule\App\Kernel\Kernel::module();

        $block = [
            'kind'   => 'run',
            'id'     => $id,
            'status' => (string)$run['status'],
            'title'  => $this->t('run_title', ['id' => $id, 'source' => strtoupper((string)$run['source'])]) . ' · ' . ($run['started_at'] ?: $run['created_at']),
            // what the runner (resources/assets/js/pages/migration.js) starts with
            'runner' => [
                'id'       => $id,
                'batchUrl' => Page::pageUrl('migration', 'batch'),
                'progress' => [
                    'id' => $id, 'status' => (string)$run['status'], 'percent' => $status['percent'], 'jobs' => $status['jobs'],
                    'finished' => $status['finished'] && !$isActive, 'stale' => $status['stale'], 'error' => (string)$run['last_error'],
                ],
                'states'   => [
                    'preparing' => $this->t('state_preparing'), 'running' => $this->t('state_running'), 'switching' => $this->t('state_switching'),
                    'completed' => $this->t('state_completed'), 'failed' => $this->t('state_failed'), 'rolled_back' => $this->t('state_rolled_back'),
                ],
            ],
            'stale'        => (bool)$status['stale'],
            'verify'       => null,
            'exceptions'   => [],
            'exceptionsTitle' => '',
            'cronRemove'   => '',
            'cronAdd'      => '',
            'deactivated'  => '',
            'autoRollback' => !empty($summary['auto_rollback']) ? $this->t('auto_rollback', ['reason' => (string)$summary['auto_rollback']['reason']]) : '',
            'errorReport'  => !empty($summary['auto_rollback']) || $run['status'] === Store::RUN_FAILED,
            'hasBackups'   => (bool)$run['backup_prefix'],
            'backupsUntil' => $this->t('backups_until', ['date' => (string)$run['backup_expires_at']]),
            'canRollback'  => $run['backup_prefix'] && (in_array($run['status'], [Store::RUN_COMPLETED, Store::RUN_FAILED], true) || $status['stale']),
        ];

        // what the check after the run said, and the legacy addon's switch-off, no longer hold once it is rolled back
        $undone = $run['status'] === Store::RUN_ROLLED_BACK;
        if(!empty($summary['verify']) && !$undone)
        {
            $v               = $summary['verify'];
            $block['verify'] = [
                'tone'     => $v['ok'] ? 'ok' : 'warn',
                'text'     => $this->t('verify_result', ['checked' => (int)$v['checked'], 'active' => (int)$v['active_ssl'], 'left' => (int)$v['legacy_rows_left']]),
                'problems' => $v['problems'] ? implode('; ', (array)$v['problems']) : '',
            ];
        }
        if(!empty($summary['exceptions']))
        {
            $block['exceptionsTitle'] = $this->t('exceptions', ['n' => count($summary['exceptions'])]);
            foreach(array_slice($summary['exceptions'], 0, 300) as $e)
            {
                $block['exceptions'][] = [
                    'object' => isset($e['service_id']) ? 'service #' . $e['service_id'] : 'product #' . ($e['product_id'] ?? ''),
                    'status' => (string)$e['status'],
                    'reason' => (string)$e['reason'],
                ];
            }
        }
        if(!empty($summary['cron_lines']) && $run['status'] === Store::RUN_COMPLETED)
        {
            $block['cronRemove'] = implode("\n", (array)$summary['cron_lines']);
            $block['cronAdd']    = implode("\n", array_map(function($job) use ($module) {
                return '*/5 * * * * php -q {WHMCS}/modules/addons/' . $module . '/cron/cron.php ' . $job;
            }, ['ProductPricingUpdate', 'SSLCertificates', 'RenewalNotifyCertificate', 'ReSyncProducts', 'SSLAutoInstall']));
        }
        if(!empty($summary['legacy_deactivated']) && !$undone)
        {
            $block['deactivated'] = $this->t('legacy_deactivated', ['result' => $summary['legacy_deactivated']['ok'] ? $this->t('yes') : $this->t('no')]);
        }

        return $block;
    }

    /** "Active 12 · Pending 3" */
    protected static function counts(array $counts): string
    {
        $parts = [];
        foreach($counts as $name => $count)
        {
            $parts[] = $name . ' ' . $count;
        }

        return implode(' · ', $parts) ?: '—';
    }

    protected function t(string $key, array $replace = []): string
    {
        return $this->lang->get(self::TEXTS . $key, $replace);
    }
}
