<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\Card;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\CronStatus;

/**
 * System health: last run of the module cron and each scheduled task
 */
class SystemCheckWidget extends Card
{
    public function loadHtml(): void
    {
        $ok = CronStatus::ok();
        $this->start('pulse', $this->translate('title'), $ok ? Card::status('ok', $this->translate('ok')) : Card::status('bad', $this->translate('attention')));

        $html = '<p class="tssggs-label" id="tssggs-health">' . Card::e($this->translate('tasks')) . '</p><ul class="tssggs-tasks">';
        foreach(CronStatus::rows() as $task => $row)
        {
            if($row['last'] === null)
            {
                $state = '<span class="is-bad">' . Card::icon('close') . Card::e($this->translate('never')) . '</span>';
            }
            elseif($row['error'] !== '')
            {
                $state = '<span class="is-warn" title="' . Card::e($row['error']) . '">' . Card::icon('alert-outline') . Card::e($row['last']) . '</span>';
            }
            else
            {
                $state = '<span class="is-ok">' . Card::icon('check') . Card::e($row['last']) . '</span>';
            }
            $html .= '<li><span class="tssggs-tasks__name">' . Card::e($task) . '</span>' . $state . '</li>';
            if($row['error'] !== '')
            {
                $html .= '<li class="tssggs-tasks__error">' . Card::e($this->translate('error') . ': ' . $row['error']) . '</li>';
            }
        }
        $html .= '</ul>' . Card::link('addonmodules.php?module=TSSGGSModule&mg-page=settings&settings-page=cronSettings', $this->translate('review'), 'is-block');

        $this->body($html);
    }
}
