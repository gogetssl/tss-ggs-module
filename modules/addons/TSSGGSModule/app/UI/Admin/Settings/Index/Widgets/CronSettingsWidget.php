<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\Libs\Support\CronLauncher;
use ModulesGarden\TSSGGSModule\App\Libs\Support\Icons;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\CronStatus;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms\CronConfigurationForm;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;

/**
 * CRON settings: a live panel (is the cron running, what starts it, one lamp per job), ONE cron line for the whole
 * module (RunAll starts every job when it is due), then the form with one card per job (description, status, how often it runs).
 */
class CronSettingsWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));
        $this->setIcon('file-clock-outline');
        $this->setSubtitle($this->translate('subtitle'));
        $this->setCss('tssggs-cronpanel');

        $script = dirname(__DIR__, 6) . '/cron/cron.php';
        $line   = '*/5 * * * * php -q ' . $script . ' RunAll';
        $copied = addslashes($this->translate('copied'));
        $copy   = addslashes($this->translate('copy'));

        $live = $this->live();
        if($live !== '')
        {
            $this->addElement((new Text())->setText($live));
        }

        $this->addElement((new Text())->setText(
            '<div class="tssggs-cronone"><div class="tssggs-cronone__title">' . Icons::svg('console') . '' . $this->translate('oneLineTitle') . '</div>'
            . '<p>' . $this->translate('oneLineInfo') . '</p>'
            . '<div class="tssggs-cronone__code"><code>' . htmlspecialchars($line) . '</code>'
            . '<button type="button" class="tssggs-copy" onclick="var b=this,o=b.innerHTML;navigator.clipboard.writeText(b.previousElementSibling.textContent);b.textContent=\'' . $copied
            . '\';setTimeout(function(){b.innerHTML=o},1500)">' . Icons::svg('content-copy') . '' . $this->translate('copy') . '</button></div></div>'
        ));

        $this->addElement(new CronConfigurationForm());
    }

    /**
     * The live panel on top: is the cron running and what starts it, a countdown to the next start, one lamp per job
     * (green healthy, red failing, grey never ran) and how many jobs need attention. Styles: theme.css .tssggs-cronlive
     */
    protected function live(): string
    {
        try
        {
            $status = CronLauncher::status();
            $rows   = CronStatus::rows();
        }
        catch(\Throwable $e)
        {
            return '';
        }

        $esc  = function($v) {
            return htmlspecialchars((string)$v, ENT_QUOTES);
        };
        $time = function($ts) {
            return $ts ? date('Y-m-d H:i', $ts) : '';
        };
        $next = 0;
        switch($status['mode'])
        {
            case 'own':
                $state = 'ok';
                $title = $this->translate('liveRunning');
                $sub   = $this->translate('liveByOwn', [':time' => '<code>' . $esc($time($status['job'])) . '</code>']);
                $next  = $status['job'] + 300;
                break;
            case 'whmcs':
                $state = 'ok';
                $title = $this->translate('liveRunning');
                $sub   = $this->translate('liveByWhmcs', [':time' => '<code>' . $esc($time($status['started'])) . '</code>']);
                $next  = $status['started'] + 300;
                break;
            case 'whmcs_rare':
                $state = 'warn';
                $title = $this->translate('liveDelayed');
                $sub   = $esc($this->translate('statusWhmcsRare', [':time' => $time($status['started'])]));
                break;
            case 'blocked':
                $state = 'bad';
                $title = $this->translate('liveStopped');
                $sub   = $esc($this->translate($status['reason'] === 'windows' ? 'statusWindows' : 'statusNoExec'));
                break;
            default:
                $state = 'bad';
                $title = $this->translate('liveStopped');
                $sub   = $esc($status['job'] ? $this->translate('statusStopped', [':time' => $time($status['job'])]) : $this->translate('statusNever'));
        }

        // the countdown runs in the browser; the page inserts this HTML dynamically (no <script>), so an image's error handler starts it
        $countdown = '';
        if($next)
        {
            $left      = max(0, $next - time());
            $countdown = '<div class="tssggs-cronlive__next">' . $esc($this->translate('liveNextRun')) . ' <b data-left="' . $left . '" data-due="' . $esc($this->translate('liveDueNow')) . '">'
                . ($left ? sprintf('%02d:%02d', intdiv($left, 60), $left % 60) : $esc($this->translate('liveDueNow'))) . '</b>'
                . '<img src="data:," alt="" style="display:none" onerror="var b=this.previousElementSibling;if(b.dataset.on||b.dataset.left==0)return;b.dataset.on=1;'
                . 'var end=Date.now()+b.dataset.left*1000,t=setInterval(function(){if(!document.body.contains(b)){clearInterval(t);return;}'
                . 'var s=Math.round((end-Date.now())/1000);if(s<=0){b.textContent=b.dataset.due;clearInterval(t);return;}'
                . 'b.textContent=String(s/60|0).padStart(2,\'0\')+\':\'+String(s%60).padStart(2,\'0\');},1000);"></div>';
        }

        $lamps   = '';
        $healthy = 0;
        $count   = 0;
        foreach(array_keys(CronConfigurationForm::JOBS) as $job)
        {
            $row      = $rows[$job] ?? ['last' => null, 'error' => ''];
            $lamp     = $row['last'] === null ? 'idle' : ($row['error'] !== '' ? 'bad' : 'ok');
            $healthy += $lamp === 'ok' ? 1 : 0;
            $lamps   .= '<li class="is-' . $lamp . '" style="--i:' . $count++ . '"><span></span>' . $esc($this->translate('lamp_' . $job)) . '</li>';
        }
        $attention = $count - $healthy;
        $summary   = '<span>' . $esc($this->translate('liveHealthy', [':count' => $healthy])) . '</span>'
            . ($attention ? '<b>' . $esc($this->translate($attention === 1 ? 'liveAttentionOne' : 'liveAttention', [':count' => $attention])) . '</b>' : '');

        return '<div class="tssggs-cronlive is-' . $state . '" style="--n:' . $count . '">'
            . '<div class="tssggs-cronlive__top"><span class="tssggs-cronlive__dot"></span><div class="tssggs-cronlive__copy">'
            . '<div class="tssggs-cronlive__title">' . $esc($title) . '</div><div class="tssggs-cronlive__sub">' . $sub . '</div></div>' . $countdown . '</div>'
            . '<div class="tssggs-cronlive__jobs"><ul>' . $lamps . '</ul><div class="tssggs-cronlive__sum">' . $summary . '</div></div></div>';
    }
}
