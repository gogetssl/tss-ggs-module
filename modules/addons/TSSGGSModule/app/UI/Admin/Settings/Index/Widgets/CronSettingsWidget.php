<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\Libs\CronLauncher;
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms\CronConfigurationForm;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;

/**
 * CRON settings: ONE cron line for the whole module (RunAll starts every job when it is due), then the form with one
 * card per job (description, status, how often it runs).
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

        $this->addElement((new Text())->setText(
            '<div class="tssggs-cronone"><div class="tssggs-cronone__title">' . Icons::svg('console') . '' . $this->translate('oneLineTitle') . '</div>'
            . '<p>' . $this->translate('oneLineInfo') . '</p>'
            . '<div class="tssggs-cronone__code"><code>' . htmlspecialchars($line) . '</code>'
            . '<button type="button" class="tssggs-copy" onclick="var b=this,o=b.innerHTML;navigator.clipboard.writeText(b.previousElementSibling.textContent);b.textContent=\'' . $copied
            . '\';setTimeout(function(){b.innerHTML=o},1500)">' . Icons::svg('content-copy') . '' . $this->translate('copy') . '</button></div>'
            . $this->status() . '</div>'
        ));

        $this->addElement(new CronConfigurationForm());
    }

    /** whether the jobs actually run and what starts them: the reseller's own line, the WHMCS cron, or nothing */
    protected function status(): string
    {
        try
        {
            $status = CronLauncher::status();
        }
        catch(\Throwable $e)
        {
            return '';
        }

        $time = function($ts) {
            return $ts ? date('Y-m-d H:i', $ts) : '';
        };
        switch($status['mode'])
        {
            case 'own':
                $class = 'is-ok';
                $text  = $this->translate('statusOwn', [':time' => $time($status['job'])]);
                break;
            case 'whmcs':
                $class = 'is-ok';
                $text  = $this->translate('statusWhmcs', [':time' => $time($status['started'])]);
                break;
            case 'whmcs_rare':
                $class = 'is-warn';
                $text  = $this->translate('statusWhmcsRare', [':time' => $time($status['started'])]);
                break;
            case 'blocked':
                $class = 'is-bad';
                $text  = $this->translate($status['reason'] === 'windows' ? 'statusWindows' : 'statusNoExec');
                break;
            default:
                $class = 'is-bad';
                $text  = $status['job'] ? $this->translate('statusStopped', [':time' => $time($status['job'])]) : $this->translate('statusNever');
        }

        return '<div class="tssggs-note tssggs-cronone__status ' . $class . '">' . htmlspecialchars((string)$text) . '</div>';
    }
}
