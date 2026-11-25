<?php

namespace TSSGGSModule\App\Pages\Admin;

use TSSGGSModule\App\Kernel\Layout;
use TSSGGSModule\App\Kernel\Page;
use TSSGGSModule\App\Kernel\Response;
use TSSGGSModule\App\Libs\Dashboard\Announcements;
use TSSGGSModule\App\Libs\Dashboard\CronStatus;
use TSSGGSModule\App\Libs\Dashboard\NextSteps;
use TSSGGSModule\App\Libs\Dashboard\Pipeline;
use TSSGGSModule\App\Libs\Dashboard\SalesSummary;
use TSSGGSModule\App\Libs\Dashboard\SupportReport;
use TSSGGSModule\App\Libs\Gateway\GatewayHealth;
use TSSGGSModule\App\Libs\Subscriptions\AutomatedSsl;
use TSSGGSModule\App\Libs\Support\Edition;
use TSSGGSModule\App\Libs\Support\GeneralSettings;
use TSSGGSModule\App\Libs\Support\Helpers;
use TSSGGSModule\App\Libs\Support\WhmcsModuleLog;
use TSSGGSModule\App\Libs\Update\Release;
use TSSGGSModule\App\Libs\Update\State;
use TSSGGSModule\App\Migration\Choice;
use TSSGGSModule\App\Migration\Detector;
use TSSGGSModule\App\Migration\Store;

/**
 * The module dashboard: heading, "new version" note, setup strip, KPI tiles, then rows of a wide and a narrow panel:
 * pipeline (or the Automated SSL banner) + system health, needs attention + API connection, sales and margin +
 * migration, announcements + help.
 *
 * The page itself is quick. The panels that read all the sales or ask the vendor (PANELS) are loaded one by one
 * after the page is shown (action "panel"); each has its own template admin/home/<panel>.tpl.
 * Styles: dashboard.css and theme.css (.tssggs-dash).
 */
class Home extends Page
{
    const LANG       = 'admin.home.index';
    const BODY_CLASS = 'tssggs-dash';
    const HEADING    = false;

    /** panels loaded after the page: name => method that gives the template's data */
    const PANELS = ['kpis', 'pipeline', 'attention', 'gateway', 'overview', 'announcements', 'support'];

    /** below this margin on cost (%) the sales panel suggests raising prices */
    const LOW_MARGIN = 10;

    /** the migration panel is removed this many days after a completed migration */
    const MIGRATION_DAYS = 14;

    /** scheduled tasks: icon in the system health panel */
    const TASK_ICONS = ['ProductPricingUpdate' => 'tag-outline', 'SSLCertificatesProcessing' => 'timer-sand', 'SSLCertificates' => 'sync',
                        'RenewalNotifyCertificate' => 'bell-outline', 'SSLAutoInstall' => 'web'];

    /** edition => support contacts (no phone: the row is left out) */
    const CONTACTS = [
        Edition::TSS => ['email' => 'support@thesslstore.com', 'phone' => '727-388-4240', 'docs' => 'https://github.com/thestores/whmcs/'],
        Edition::GGS => ['email' => 'support@gogetssl.com', 'phone' => '', 'docs' => 'https://github.com/gogetssl/whmcs'],
    ];

    public function title(): string
    {
        return $this->t('home_container.title');
    }

    public function index()
    {
        return $this->view('admin/home/index', [
            'update'       => $this->update(),
            'steps'        => NextSteps::visible() ? NextSteps::steps() : [],
            // the Automated SSL banner until a CaaS and an AIS product are enabled, then the certificate pipeline
            'showPipeline' => AutomatedSsl::enabled(),
            'health'       => $this->health(),
            'migration'    => $this->migration(),
            'edition'      => Edition::name(),
            'version'      => Layout::version(),
        ]);
    }

    /** GET name: one of the panels that are loaded after the page, as the fragment that replaces its spinner */
    public function panel()
    {
        $name = $this->request->oneOf('name', self::PANELS);
        if($name === '')
        {
            return Response::fragment(Layout::notFound($this), 404);
        }

        return $this->fragment('admin/home/' . $name, $this->$name());
    }

    /* ---- activity report for support ---- */

    /** GET: the "Send report" dialog */
    public function report()
    {
        return $this->fragment('admin/home/report', SupportReport::facts() + ['error' => '', 'issueDetails' => '']);
    }

    /** POST fromDate, toDate, issueDetails */
    public function postSendReport()
    {
        $form = ['fromDate' => $this->request->string('fromDate'), 'toDate' => $this->request->string('toDate'), 'issueDetails' => $this->request->string('issueDetails')];
        try
        {
            if($form['issueDetails'] === '')
            {
                throw new \RuntimeException($this->t('forms.reporting_form.details_required'));
            }
            SupportReport::send($form);
        }
        catch(\Throwable $e)
        {
            return $this->fragment('admin/home/report', $form + SupportReport::facts() + ['error' => $e->getMessage()])->intoDialog();
        }

        return $this->fragment('admin/home/report_sent', ['text' => $this->t('providers.reporting_provider.reportSent', ['address' => SupportReport::supportAddress()])])->intoDialog();
    }

    /** GET fromDate, toDate, issueDetails: the module log of those dates as a text file */
    public function log()
    {
        return Response::download(SupportReport::log([
            'fromDate'     => $this->request->string('fromDate'),
            'toDate'       => $this->request->string('toDate'),
            'issueDetails' => $this->request->string('issueDetails'),
        ]), 'log.txt', 'text/plain; charset=utf-8');
    }

    /* ---- parts of the page ---- */

    /**
     * "Version X is available". The cron looks once a day; without a cron the dashboard looks itself when the last
     * check is older than three days.
     */
    protected function update(): string
    {
        try
        {
            $state = State::get();
            if(time() - (int)($state['checkedAt'] ?? 0) > 3 * 86400)
            {
                $state = Release::check(true);
            }

            return Release::available($state)
                ? $this->lang->get('admin.module_update.banner', ['version' => (string)$state['version'], 'installed' => State::installedVersion()])
                : '';
        }
        catch(\Throwable $e)
        {
            return '';
        }
    }

    /** System health: a row per scheduled task with its state */
    protected function health(): array
    {
        // the pending orders check runs every 5 minutes and is not listed (its failures show under "Needs your attention")
        $rows   = array_diff_key(CronStatus::rows(), ['SSLCertificatesProcessing' => true]);
        $latest = null;
        $ok     = true;
        $tasks  = [];
        foreach($rows as $task => $row)
        {
            $ok     = $ok && $row['last'] !== null && $row['error'] === '';
            $latest = $row['last'] !== null && ($latest === null || strcmp($row['last'], $latest) > 0) ? $row['last'] : $latest;
            if($row['last'] === null)
            {
                $state = ['tone' => 'bad', 'label' => $this->t('widgets.system_check_widget.never'), 'pillIcon' => 'close', 'title' => ''];
            }
            elseif($row['error'] !== '')
            {
                $state = ['tone' => 'bad', 'label' => $this->t('widgets.system_check_widget.failing'), 'pillIcon' => 'alert-outline', 'title' => $row['error']];
            }
            else
            {
                $state = ['tone' => 'ok', 'label' => $this->when($row['last']), 'pillIcon' => 'check', 'title' => $row['last']];
            }
            $tasks[] = ['icon' => self::TASK_ICONS[$task] ?? 'cog-outline', 'name' => $this->taskName('system_check_widget', $task)] + $state;
        }

        return [
            'ok'           => $ok,
            'latest'       => $latest !== null ? $this->when($latest) : '',
            'tasks'        => $tasks,
            // the module sends its requests to the WHMCS Module Log, but WHMCS has that log switched off
            'moduleLogOff' => GeneralSettings::enabled('moduleLog') && !WhmcsModuleLog::enabled(),
            'moduleLogLabel' => $this->t('widgets.system_check_widget.moduleLog_off'),
            'moduleLogTitle' => $this->t('widgets.system_check_widget.moduleLog_off_title'),
            'logsUrl'      => self::pageUrl('settings', 'index', ['settings-page' => 'logsSettings']),
            'cronUrl'      => self::pageUrl('settings', 'index', ['settings-page' => 'cronSettings']),
        ];
    }

    /** Legacy modules found, the last migration run, backup expiry; null when there is nothing to show */
    protected function migration(): ?array
    {
        try
        {
            $run     = Store::latestRun();
            $removed = $run && $run['status'] === 'completed' && !empty($run['finished_at']) ? strtotime((string)$run['finished_at']) + self::MIGRATION_DAYS * 86400 : null;
            if(($removed !== null && time() >= $removed) || (!Detector::anyFound() && !$run))
            {
                return null;
            }

            $sources = [];
            foreach(Detector::detect() as $source => $info)
            {
                $choice    = Choice::get($source);
                $sources[] = [
                    'name'     => $info['label'] . ' ' . $info['version'],
                    'choice'   => $choice ? $this->t('widgets.migration_widget.choice_' . $choice) : '',
                    'services' => (int)($info['counts']['services']['Active'] ?? 0),
                ];
            }
            $counts = $run ? Store::jobCounts((int)$run['id']) : null;

            return [
                'sources' => $sources,
                'run'     => $run ? [
                    'id'      => (int)$run['id'],
                    'status'  => (string)$run['status'],
                    'tone'    => ['completed' => 'ok', 'failed' => 'bad', 'rolled_back' => 'muted'][$run['status']] ?? 'warn',
                    'icon'    => ['completed' => 'check', 'failed' => 'close', 'rolled_back' => 'undo'][$run['status']] ?? 'progress-clock',
                    'counts'  => $counts,
                    'backups' => $run['backup_prefix'] && $run['backup_expires_at'] ? substr((string)$run['backup_expires_at'], 0, 10) : '',
                    'removed' => $removed !== null ? date('Y-m-d', $removed) : '',
                ] : null,
                'days'    => self::MIGRATION_DAYS,
                'runLabel' => $run ? $this->t('widgets.migration_widget.run_short', ['id' => (int)$run['id'], 'status' => $run['status']]) : '',
            ];
        }
        catch(\Throwable $e)
        {
            return null;
        }
    }

    /* ---- panels loaded after the page ---- */

    protected function kpis(): array
    {
        $sales    = SalesSummary::get();
        $currency = (string)Helpers::getDefaultCurrencyCode();
        $period   = SalesSummary::periodLabel();
        $tiles    = [];
        foreach([
            ['kpi_sales', Helpers::formatDefaultCurrency($sales['paid']), 'currency-usd', 'ok', 'credit-card-outline'],
            ['kpi_profit', Helpers::formatDefaultCurrency($sales['profit']), 'trending-up', 'info', 'chart-line'],
            ['kpi_issued', (string)$sales['issued'], 'certificate-outline', 'ok', 'calendar-month-outline'],
            ['kpi_expiring', (string)$sales['expiring'], 'timer-sand', 'warn', 'check-circle-outline'],
        ] as [$key, $value, $icon, $tone, $detailIcon])
        {
            $tiles[] = [
                'title'      => $this->t('home_container.' . $key),
                'value'      => $value,
                'icon'       => $icon,
                'tone'       => $tone,
                'detailIcon' => $detailIcon,
                'detail'     => $this->t('home_container.' . $key . '_detail', ['currency' => $currency, 'period' => $period]),
            ];
        }

        return ['tiles' => $tiles, 'period' => $period];
    }

    /** Where the orders are: waiting for the client, with the certificate authority, issued, expiring */
    protected function pipeline(): array
    {
        $data   = Pipeline::get();
        $stages = [];
        foreach(['configure' => ['file-document-outline', 'is-y'], 'validation' => ['send-outline', 'is-b'], 'issued' => ['shield-check-outline', 'is-g'], 'expiring' => ['timer-sand', 'is-r']] as $stage => [$icon, $tone])
        {
            $stages[] = ['name' => $stage, 'icon' => $icon, 'tone' => $tone, 'count' => (int)$data[$stage],
                         // the bar shows the three stages an order goes through (expiring ones are part of "issued")
                         'inBar' => $stage !== 'expiring' && $data[$stage] > 0];
        }

        return ['stages' => $stages, 'unpaid' => (int)$data['unpaid'], 'expired' => (int)$data['expired'], 'reportsUrl' => self::pageUrl('reports')];
    }

    /** Failing or never-run tasks, an unreachable vendor, orders waiting for the client - each with the page that fixes it */
    protected function attention(): array
    {
        $w     = 'widgets.attention_widget.';
        $items = [];
        $cron  = self::pageUrl('settings', 'index', ['settings-page' => 'cronSettings']);
        $api   = self::pageUrl('settings', 'index', ['settings-page' => 'apiSettings']);
        foreach(CronStatus::rows() as $task => $row)
        {
            $name = $this->taskName('attention_widget', $task);
            if($row['last'] === null)
            {
                $items[] = ['tone' => 'bad', 'icon' => 'close', 'title' => $this->t($w . 'never_title', ['task' => $name]), 'text' => $this->t($w . 'never_text'),
                            'url' => $cron, 'button' => $this->t($w . 'cron'), 'buttonIcon' => 'cog-outline'];
            }
            elseif($row['error'] !== '')
            {
                $items[] = ['tone' => 'bad', 'icon' => 'close', 'title' => $this->t($w . 'failing_title', ['task' => $name]), 'text' => $row['error'],
                            'url' => self::pageUrl('logs'), 'button' => $this->t($w . 'log'), 'buttonIcon' => 'format-list-checks'];
            }
        }
        // the edition's vendor and the connected one: unreachable, or its credentials are not entered
        foreach(Edition::vendors() as $key)
        {
            $vendor  = strtoupper($key);
            $gateway = GatewayHealth::check()[$vendor] ?? null;
            if($gateway === null || !$gateway['ok'])
            {
                $items[] = ['tone' => 'bad', 'icon' => 'power-plug-off-outline',
                            'title' => $this->t($w . ($gateway === null ? 'gateway_missing_title' : 'gateway_title'), ['gateway' => Helpers::vendorToDisplay($vendor)]),
                            'text'  => $gateway === null ? $this->t($w . 'gateway_missing_text') : (string)$gateway['error'],
                            'url'   => $api, 'button' => $this->t($w . 'api'), 'buttonIcon' => 'key-outline'];
            }
        }
        $waiting = (int)Pipeline::get()['configure'];
        if($waiting > 0)
        {
            $items[] = ['tone' => 'warn', 'icon' => 'file-document-outline', 'title' => $this->t($w . ($waiting === 1 ? 'configure_title_one' : 'configure_title'), ['n' => $waiting]),
                        'text' => $this->t($w . 'configure_text'), 'url' => self::pageUrl('reports'), 'button' => $this->t($w . 'orders'), 'buttonIcon' => 'arrow-right'];
        }

        return ['items' => $items, 'count' => $this->t($w . (count($items) === 1 ? 'count_one' : 'count'), ['n' => count($items)])];
    }

    /** Reachability and response time of the vendor API(s); recheck=1 asks again instead of using the last answer */
    protected function gateway(): array
    {
        $results = GatewayHealth::check($this->request->int('recheck') === 1);
        $allOk   = true;
        $vendors = [];
        foreach(Edition::vendors() as $key)
        {
            $vendor    = strtoupper($key);
            $row       = $results[$vendor] ?? null;
            $allOk     = $allOk && $row && $row['ok'];
            $vendors[] = [
                'name'       => Helpers::vendorToDisplay($vendor),
                'configured' => $row !== null,
                'ok'         => $row && $row['ok'],
                'error'      => $row && !$row['ok'] ? (string)$row['error'] : '',
                'seconds'    => $row ? number_format(((int)$row['ms']) / 1000, 1) : '',
            ];
        }

        $w = 'widgets.gateway_widget.';

        return ['vendors' => $vendors, 'allOk' => $allOk, 'labels' => ['notConfigured' => $this->t($w . 'not_configured'), 'connected' => $this->t($w . 'connected'), 'unreachable' => $this->t($w . 'unreachable')], 'apiUrl' => self::pageUrl('settings', 'index', ['settings-page' => 'apiSettings'])];
    }

    /** Sales and margin of the dashboard period */
    protected function overview(): array
    {
        $sales    = SalesSummary::get();
        $invoiced = $sales['paid'] + $sales['unpaid'] + $sales['overdue'];
        // cost and margin over the invoiced rows whose reseller cost is known (an unknown cost is not a cost of 100%)
        $cost     = $sales['cost'];
        $profit   = $sales['costedSales'] - $cost;
        $margin   = $cost > 0 ? $profit / $cost * 100 : 0;
        $w        = 'widgets.overview_widget.';

        $note = $this->t($w . 'split', [
            'period'  => SalesSummary::periodLabel(),
            'paid'    => html_entity_decode((string)Helpers::formatDefaultCurrency($sales['paid']), ENT_QUOTES, 'UTF-8'),
            'unpaid'  => html_entity_decode((string)Helpers::formatDefaultCurrency($sales['unpaid']), ENT_QUOTES, 'UTF-8'),
            'overdue' => html_entity_decode((string)Helpers::formatDefaultCurrency($sales['overdue']), ENT_QUOTES, 'UTF-8'),
        ]);
        if($cost > 0 && $margin < self::LOW_MARGIN)
        {
            $note .= ' ' . $this->t($w . 'low', ['margin' => self::LOW_MARGIN]);
        }

        return [
            'tiles' => [
                ['icon' => 'currency-usd', 'title' => $this->t($w . 'invoiced'), 'value' => Helpers::formatDefaultCurrency($invoiced)],
                ['icon' => 'package-variant-closed', 'title' => $this->t($w . 'cost'), 'value' => Helpers::formatDefaultCurrency($cost)],
                ['icon' => 'trending-up', 'title' => $this->t($w . 'profit'), 'value' => Helpers::formatDefaultCurrency($profit)],
                ['icon' => 'tag-outline', 'title' => $this->t($w . 'margin'), 'value' => number_format($margin, 1) . '%'],
            ],
            'bar'   => max(0, min(100, round($margin, 1))),
            'note'  => $note,
        ];
    }

    protected function announcements(): array
    {
        try
        {
            return ['items' => Announcements::latest(), 'error' => ''];
        }
        catch(\Throwable $e)
        {
            return ['items' => [], 'error' => $this->t('widgets.announcements_widget.unavailable', ['error' => $e->getMessage()])];
        }
    }

    /** Support contacts, documentation, the partner growth manager(s) of the configured vendors */
    protected function support(): array
    {
        $country  = (string)\WHMCS\Config\Setting::getValue('DefaultCountry');
        $managers = [];
        foreach(Helpers::getConfiguredApis() as $api)
        {
            try
            {
                $manager = (array)$api->getManager($country);
            }
            catch(\Throwable $e)
            {
                continue;
            }
            $email = (string)($manager['email'] ?? '');
            if($email !== '' && !isset($managers[$email]))
            {
                $managers[$email] = ['name' => (string)($manager['name'] ?? $email), 'email' => $email];
            }
        }

        return ['contact' => self::CONTACTS[Edition::get()] ?? self::CONTACTS[Edition::TSS], 'managers' => array_values($managers)];
    }

    /* ---- helpers ---- */

    /** The clear name of a scheduled task (its command name when there is no text for it) */
    protected function taskName(string $widget, string $task): string
    {
        $key = static::LANG . '.widgets.' . $widget . '.name_' . $task;

        return $this->lang->has($key) ? $this->lang->get($key) : $task;
    }

    /** "17:00" today, "Yesterday 13:30", "1 Nov 13:30" this year, "1 Nov 2025 13:30" before */
    protected function when(string $datetime): string
    {
        $time = strtotime($datetime);
        $day  = date('Y-m-d', $time);
        if($day === date('Y-m-d'))
        {
            return date('H:i', $time);
        }
        if($day === date('Y-m-d', strtotime('-1 day')))
        {
            return $this->t('widgets.system_check_widget.yesterday', ['time' => date('H:i', $time)]);
        }

        return date(date('Y', $time) === date('Y') ? 'j M H:i' : 'j M Y H:i', $time);
    }
}
