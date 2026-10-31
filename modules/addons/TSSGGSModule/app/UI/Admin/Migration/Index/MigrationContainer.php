<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Migration\Index;

use ModulesGarden\TSSGGSModule\App\Http\Admin\Migration as MigrationController;
use ModulesGarden\TSSGGSModule\App\Migration\Choice;
use ModulesGarden\TSSGGSModule\App\Migration\Detector;
use ModulesGarden\TSSGGSModule\App\Migration\Engine;
use ModulesGarden\TSSGGSModule\App\Migration\Store;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\MigrationRunner\MigrationRunner;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Translation\TranslatorTrait;

/**
 * Admin page "Migration": legacy modules found, dry-run summary, start / progress / result, backups and rollback.
 * Plain HTML forms post to App\Http\Admin\Migration; the runner component drives the batches.
 */
class MigrationContainer extends Container implements AdminAreaInterface
{
    use TranslatorTrait;

    public function loadHtml(): void
    {
        $this->setCss('tssggs-migration');

        if(!empty($_SESSION['tssggs_migration_error']))
        {
            $this->addElement($this->html('<div class="tssggs-note is-bad">' . htmlspecialchars($_SESSION['tssggs_migration_error']) . '</div>'));
            unset($_SESSION['tssggs_migration_error']);
        }

        $active = Store::activeRun();
        if($active)
        {
            $this->addElement($this->runWidget($active, true));
        }

        $found = Detector::detect();
        if(!$found && !$active)
        {
            $widget = new Widget();
            $widget->setTitle($this->t('title'));
            $widget->addElement($this->html('<p class="tssggs-muted">' . htmlspecialchars($this->t('none_found')) . '</p>'));
            $this->addElement($widget);
        }

        foreach($found as $source => $info)
        {
            if($active && $active['source'] === $source)
            {
                continue;
            }
            $this->addElement($this->sourceWidget($source, $info));
        }

        foreach($this->pastRuns() as $run)
        {
            if($active && (int)$active['id'] === (int)$run['id'])
            {
                continue;
            }
            $this->addElement($this->runWidget($run, false));
        }
    }

    // ---- one legacy module ---- //

    protected function sourceWidget(string $source, array $info): Widget
    {
        $widget = new Widget();
        $widget->setTitle($info['label'] . ($info['version'] ? ' ' . $info['version'] : ''));
        $widget->appendCss('tssggs-migration__source');

        $c      = $info['counts'];
        $choice = Choice::get($source);
        $html   = '<div class="tssggs-cards">'
            . $this->card($this->t('card_products'), (int)$c['products']['total'], $this->t('card_products_sub', ['hidden' => (int)$c['products']['hidden'], 'retired' => (int)$c['products']['retired']]))
            . $this->card($this->t('card_services'), (int)($c['services']['Active'] ?? 0), $this->sub($c['services']))
            . $this->card($this->t('card_orders'), array_sum($c['orders']), $this->sub($c['orders']))
            . $this->card($this->t('card_subscriptions'), (int)$c['acme'] + (int)$c['ais'], 'ACME ' . (int)$c['acme'] . ' · AIS ' . (int)$c['ais'])
            . $this->card($this->t('card_autoinstall'), (int)$c['autoinstall'], $this->t('card_queue_open', ['n' => (int)$c['queue_open']]))
            . $this->card($this->t('card_unconfigured'), (int)$c['unconfigured'], $this->t('card_unconfigured_sub', ['pre' => (int)$c['unconfigured_preordered'], 'unpaid' => (int)$c['unpaid']]))
            . '</div>';

        // support band
        $band = $info['support']['band'];
        $html .= '<div class="tssggs-note is-' . ($band === 'full' ? 'ok' : ($band === 'partial' ? 'warn' : 'bad')) . '">'
            . '<b>' . htmlspecialchars($this->t('support_' . $band)) . '</b>'
            . ($info['support']['reason'] ? ' ' . htmlspecialchars($info['support']['reason']) : '')
            . (!$info['installed'] ? ' ' . htmlspecialchars($this->t('files_missing')) : '')
            . '</div>';

        $unsupported = array_filter((array)$c['unsupported']);
        if($unsupported)
        {
            $html .= '<div class="tssggs-note is-warn">' . htmlspecialchars($this->t('unsupported_found')) . ' ' . htmlspecialchars(implode(', ', array_map(function($k, $v) { return $v . ' ' . $k; }, array_keys($unsupported), $unsupported))) . '</div>';
        }
        if($c['other_ssl_modules'])
        {
            $html .= '<p class="tssggs-muted">' . htmlspecialchars($this->t('other_modules')) . ' ' . htmlspecialchars(implode(', ', array_map(function($k, $v) { return $k . ' (' . $v . ')'; }, array_keys($c['other_ssl_modules']), $c['other_ssl_modules']))) . '</p>';
        }

        $html .= '<p class="tssggs-muted">' . htmlspecialchars($this->t('choice_current')) . ' <b>' . htmlspecialchars($choice ? $this->t('choice_' . $choice) : $this->t('choice_none')) . '</b></p>';

        if($info['support']['supported'])
        {
            $token  = MigrationController::token();
            $report = 'addonmodules.php?module=TSSGGSModule&mg-page=migration&mg-action=report&source=' . $source;
            $html  .= '<div class="tssggs-actions">'
                . '<a class="lu-btn lu-btn--default" href="' . $report . '">' . htmlspecialchars($this->t('btn_report')) . '</a>'
                . '<form method="post" action="addonmodules.php?module=TSSGGSModule&mg-page=migration&mg-action=start" class="tssggs-start" onsubmit="return confirm(' . htmlspecialchars(json_encode($this->t('confirm_start')), ENT_QUOTES) . ');">'
                . '<input type="hidden" name="token" value="' . htmlspecialchars($token) . '"><input type="hidden" name="source" value="' . $source . '">'
                . '<label><input type="checkbox" name="fetch_cpanel_keys" value="1" checked> ' . htmlspecialchars($this->t('opt_keys')) . '</label>'
                . '<label><input type="checkbox" name="deactivate_legacy" value="1" checked> ' . htmlspecialchars($this->t('opt_deactivate')) . '</label>'
                . '<label><input type="checkbox" name="backup_logs" value="1" checked> ' . htmlspecialchars($this->t('opt_backup_logs')) . '</label>'
                . '<button type="submit" class="lu-btn lu-btn--success">' . htmlspecialchars($this->t('btn_start')) . '</button>'
                . '</form>'
                . '<form method="post" action="addonmodules.php?module=TSSGGSModule&mg-page=migration&mg-action=skip">'
                . '<input type="hidden" name="token" value="' . htmlspecialchars($token) . '"><input type="hidden" name="source" value="' . $source . '">'
                . '<button type="submit" class="lu-btn lu-btn--default">' . htmlspecialchars($this->t('btn_skip')) . '</button>'
                . '</form>'
                . '</div>';
        }
        else
        {
            $html .= '<div class="tssggs-note is-bad">' . htmlspecialchars($this->t('unsupported_version_hint')) . '</div>';
        }

        $widget->addElement($this->html($html));

        return $widget;
    }

    // ---- one run ---- //

    protected function runWidget(array $run, bool $isActive): Widget
    {
        $engine = new Engine($run['source']);
        $status = $engine->status((int)$run['id']);
        $widget = new Widget();
        $widget->setTitle($this->t('run_title', ['id' => (int)$run['id'], 'source' => strtoupper($run['source'])]) . ' · ' . ($run['started_at'] ?: $run['created_at']));
        $widget->appendCss('tssggs-migration__run is-' . $run['status']);

        $labels = [
            'done' => $this->t('runner_done'), 'skipped' => $this->t('runner_skipped'), 'failed' => $this->t('runner_failed'), 'pending' => $this->t('runner_pending'),
            'keepOpen' => $this->t('runner_keep_open'), 'finished' => $this->t('runner_finished'), 'reload' => $this->t('runner_reload'),
            'states' => ['preparing' => $this->t('state_preparing'), 'running' => $this->t('state_running'), 'switching' => $this->t('state_switching'),
                         'completed' => $this->t('state_completed'), 'failed' => $this->t('state_failed'), 'rolled_back' => $this->t('state_rolled_back')],
        ];
        $runner = (new MigrationRunner())
            ->setRunId((int)$run['id'])
            ->setBatchUrl('addonmodules.php?module=TSSGGSModule&mg-page=migration&mg-action=batch')
            ->setToken(MigrationController::token())
            ->setInitial([
                'id' => (int)$run['id'], 'status' => $run['status'], 'percent' => $status['percent'], 'jobs' => $status['jobs'],
                'finished' => $status['finished'] && !$isActive, 'stale' => $status['stale'], 'error' => (string)$run['last_error'],
            ])
            ->setLabels($labels);
        $widget->addElement($runner);

        $html = '';
        if($status['stale'])
        {
            $html .= '<div class="tssggs-note is-warn">' . htmlspecialchars($this->t('run_stale')) . '</div>';
        }
        $summary = $run['summary'] ?? [];
        if(!empty($summary['verify']))
        {
            $v     = $summary['verify'];
            $html .= '<div class="tssggs-note is-' . ($v['ok'] ? 'ok' : 'warn') . '">' . htmlspecialchars($this->t('verify_result', ['checked' => (int)$v['checked'], 'active' => (int)$v['active_ssl'], 'left' => (int)$v['legacy_rows_left']]))
                . ($v['problems'] ? '<br>' . htmlspecialchars(implode('; ', $v['problems'])) : '') . '</div>';
        }
        if(!empty($summary['exceptions']))
        {
            $html .= '<details class="tssggs-details"><summary>' . htmlspecialchars($this->t('exceptions', ['n' => count($summary['exceptions'])])) . '</summary><table class="tssggs-table"><thead><tr><th>' . $this->t('col_object') . '</th><th>' . $this->t('col_status') . '</th><th>' . $this->t('col_reason') . '</th></tr></thead><tbody>';
            foreach(array_slice($summary['exceptions'], 0, 300) as $e)
            {
                $obj   = isset($e['service_id']) ? 'service #' . $e['service_id'] : 'product #' . ($e['product_id'] ?? '');
                $html .= '<tr><td>' . htmlspecialchars($obj) . '</td><td>' . htmlspecialchars($e['status']) . '</td><td>' . htmlspecialchars((string)$e['reason']) . '</td></tr>';
            }
            $html .= '</tbody></table></details>';
        }
        if(!empty($summary['cron_lines']) && $run['status'] === Store::RUN_COMPLETED)
        {
            $html .= '<details class="tssggs-details"><summary>' . htmlspecialchars($this->t('cron_title')) . '</summary><p>' . htmlspecialchars($this->t('cron_remove')) . '</p><pre>' . htmlspecialchars(implode("\n", $summary['cron_lines'])) . '</pre>'
                . '<p>' . htmlspecialchars($this->t('cron_add')) . '</p><pre>' . htmlspecialchars("*/5 * * * * php -q {WHMCS}/modules/addons/TSSGGSModule/cron/cron.php ProductPricingUpdate\n*/5 * * * * php -q {WHMCS}/modules/addons/TSSGGSModule/cron/cron.php SSLCertificates\n*/5 * * * * php -q {WHMCS}/modules/addons/TSSGGSModule/cron/cron.php RenewalNotifyCertificate\n*/5 * * * * php -q {WHMCS}/modules/addons/TSSGGSModule/cron/cron.php ReSyncProducts\n*/5 * * * * php -q {WHMCS}/modules/addons/TSSGGSModule/cron/cron.php SSLCertificatesProcessing\n*/5 * * * * php -q {WHMCS}/modules/addons/TSSGGSModule/cron/cron.php SSLAutoInstall") . '</pre></details>';
        }
        if(!empty($summary['legacy_deactivated']))
        {
            $d     = $summary['legacy_deactivated'];
            $html .= '<p class="tssggs-muted">' . htmlspecialchars($this->t('legacy_deactivated', ['result' => $d['ok'] ? $this->t('yes') : $this->t('no')])) . '</p>';
        }

        $token = MigrationController::token();
        $html .= '<div class="tssggs-actions">'
            . '<a class="lu-btn lu-btn--default" href="addonmodules.php?module=TSSGGSModule&mg-page=migration&mg-action=log&id=' . (int)$run['id'] . '">' . htmlspecialchars($this->t('btn_log')) . '</a>';
        if($run['backup_prefix'])
        {
            $html .= '<span class="tssggs-muted">' . htmlspecialchars($this->t('backups_until', ['date' => (string)$run['backup_expires_at']])) . '</span>'
                . '<form method="post" action="addonmodules.php?module=TSSGGSModule&mg-page=migration&mg-action=extendBackups"><input type="hidden" name="token" value="' . htmlspecialchars($token) . '"><input type="hidden" name="id" value="' . (int)$run['id'] . '"><button type="submit" class="lu-btn lu-btn--default">' . htmlspecialchars($this->t('btn_extend')) . '</button></form>'
                . '<form method="post" action="addonmodules.php?module=TSSGGSModule&mg-page=migration&mg-action=deleteBackups" onsubmit="return confirm(' . htmlspecialchars(json_encode($this->t('confirm_delete')), ENT_QUOTES) . ');"><input type="hidden" name="token" value="' . htmlspecialchars($token) . '"><input type="hidden" name="id" value="' . (int)$run['id'] . '"><button type="submit" class="lu-btn lu-btn--default">' . htmlspecialchars($this->t('btn_delete_backups')) . '</button></form>';
            if(in_array($run['status'], [Store::RUN_COMPLETED, Store::RUN_FAILED], true) || $status['stale'])
            {
                $html .= '<form method="post" action="addonmodules.php?module=TSSGGSModule&mg-page=migration&mg-action=rollback" onsubmit="return confirm(' . htmlspecialchars(json_encode($this->t('confirm_rollback')), ENT_QUOTES) . ');"><input type="hidden" name="token" value="' . htmlspecialchars($token) . '"><input type="hidden" name="id" value="' . (int)$run['id'] . '"><button type="submit" class="lu-btn lu-btn--danger">' . htmlspecialchars($this->t('btn_rollback')) . '</button></form>';
            }
        }
        else
        {
            $html .= '<span class="tssggs-muted">' . htmlspecialchars($this->t('backups_deleted')) . '</span>';
        }
        $html .= '</div>';

        $widget->addElement($this->html($html));

        return $widget;
    }

    protected function pastRuns(): array
    {
        Store::createTables();
        $rows = \WHMCS\Database\Capsule::table(Store::TABLE_RUNS)->where('status', '<>', Store::RUN_DRY_RUN)->orderBy('id', 'desc')->limit(5)->get();
        $runs = [];
        foreach($rows as $row)
        {
            $runs[] = Store::getRun((int)$row->id);
        }

        return $runs;
    }

    // ---- small helpers ---- //

    protected function card(string $label, int $value, string $sub): string
    {
        return '<div class="tssggs-card"><div class="tssggs-card__label">' . htmlspecialchars($label) . '</div><div class="tssggs-card__value">' . $value . '</div><div class="tssggs-card__sub">' . htmlspecialchars($sub) . '</div></div>';
    }

    protected function sub(array $counts): string
    {
        $parts = [];
        foreach($counts as $k => $v)
        {
            $parts[] = $k . ' ' . $v;
        }

        return implode(' · ', $parts) ?: '—';
    }

    protected function html(string $html): Text
    {
        $text = new Text();
        $text->setCss('tssggs-html');
        $text->setText($html);

        return $text;
    }

    protected function t(string $key, array $replace = []): string
    {
        return (string)$this->translate($key, $replace, ['admin.migration']);
    }
}
