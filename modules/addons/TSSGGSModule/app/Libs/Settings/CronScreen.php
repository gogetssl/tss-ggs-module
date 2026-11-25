<?php

namespace TSSGGSModule\App\Libs\Settings;

use TSSGGSModule\App\Kernel\Kernel;
use TSSGGSModule\App\Kernel\Lang;
use TSSGGSModule\App\Libs\Dashboard\CronStatus;
use TSSGGSModule\App\Libs\Support\CronLauncher;

/**
 * What Settings > CRON shows besides the frequency fields: the live panel (is the cron running, what starts it, the
 * countdown to the next start, a lamp per job), the one cron line of the module, and per job when it ran last.
 */
class CronScreen
{
    const WIDGET = 'admin.settings.index.widgets.cron_settings_widget.';

    /** job => [setting key of its frequency (null: runs with every start), choices] */
    const JOBS = [
        'ProductPricingUpdate'     => ['cron1', ['1d', '1w', '1mo', 'never']],
        'SSLCertificates'          => ['cron2', ['5m', '10m', '30m', '1h', '3h', '12h', '1d', 'never']],
        'RenewalNotifyCertificate' => ['cron3', ['1d', 'never']],
        'SSLAutoInstall'           => [null, []],
    ];

    const LABELS = [
        '5m' => 'every 5 minutes', '10m' => 'every 10 minutes', '30m' => 'every 30 minutes', '1h' => 'every hour', '3h' => 'every 3 hours',
        '12h' => 'every 12 hours', '1d' => 'every day', '1w' => 'every week', '1mo' => 'every month', 'never' => 'never',
    ];

    public static function data(Lang $lang): array
    {
        $status = CronStatus::rows();
        $jobs   = [];
        foreach(self::JOBS as $job => [$key, $choices])
        {
            $row     = $status[$job] ?? ['last' => null, 'error' => ''];
            $jobs[]  = [
                'name'    => $job,
                'state'   => $row['last'] === null ? 'idle' : ($row['error'] !== '' ? 'bad' : 'ok'),
                'last'    => $row['last'],
                'error'   => (string)$row['error'],
                'key'     => $key,
                'choices' => array_intersect_key(self::LABELS, array_flip($choices)),
            ];
        }

        return [
            'live' => self::live($lang, $jobs),
            // the title's text carries its own <b>
            'oneLineTitle' => strip_tags($lang->get(self::WIDGET . 'oneLineTitle'), '<b>'),
            'line' => '*/5 * * * * php -q ' . Kernel::path('cron', 'cron.php') . ' RunAll',
            'jobs' => $jobs,
        ];
    }

    /** @return array|null state (ok | warn | bad), title, sub (escaped HTML), left (seconds to the next start, 0: no countdown), healthy, attention */
    protected static function live(Lang $lang, array $jobs): ?array
    {
        try
        {
            $status = CronLauncher::status();
        }
        catch(\Throwable $e)
        {
            return null;
        }
        $t    = function($key, array $vars = []) use ($lang) {
            return $lang->get(self::WIDGET . $key, $vars);
        };
        $esc  = function($value) {
            return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
        };
        $time = function($ts) {
            return $ts ? date('Y-m-d H:i', $ts) : '';
        };
        // the time inside the text is shown as code
        $withTime = function($key, $ts) use ($t, $esc, $time) {
            return str_replace('[[time]]', '<code>' . $esc($time($ts)) . '</code>', $esc($t($key, ['time' => '[[time]]'])));
        };

        $next = 0;
        switch($status['mode'])
        {
            case 'own':
                [$state, $title, $sub] = ['ok', $t('liveRunning'), $withTime('liveByOwn', $status['job'])];
                $next = $status['job'] + 300;
                break;
            case 'whmcs':
                [$state, $title, $sub] = ['ok', $t('liveRunning'), $withTime('liveByWhmcs', $status['started'])];
                $next = $status['started'] + 300;
                break;
            case 'whmcs_rare':
                [$state, $title, $sub] = ['warn', $t('liveDelayed'), $esc($t('statusWhmcsRare', ['time' => $time($status['started'])]))];
                break;
            case 'blocked':
                [$state, $title, $sub] = ['bad', $t('liveStopped'), $esc($t($status['reason'] === 'windows' ? 'statusWindows' : 'statusNoExec'))];
                break;
            default:
                [$state, $title, $sub] = ['bad', $t('liveStopped'), $esc($status['job'] ? $t('statusStopped', ['time' => $time($status['job'])]) : $t('statusNever'))];
        }

        $healthy   = count(array_filter($jobs, function($job) {
            return $job['state'] === 'ok';
        }));
        $attention = count($jobs) - $healthy;

        return [
            'state'     => $state,
            'title'     => $title,
            'sub'       => $sub,
            'countdown' => (bool)$next,
            'left'      => $next ? max(0, $next - time()) : 0,
            'healthy'   => $t('liveHealthy', ['count' => $healthy]),
            'attention' => $attention ? $t($attention === 1 ? 'liveAttentionOne' : 'liveAttention', ['count' => $attention]) : '',
        ];
    }
}
