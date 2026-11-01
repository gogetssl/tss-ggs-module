<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\Libs\GatewayHealth;
use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
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
            // clear name of the task (the command name stays in the tooltip)
            $name  = $this->translate('name_' . $task);
            $name  = strpos($name, 'name_') !== false ? $task : $name;
            $html .= '<li><span class="tssggs-tasks__name" title="' . Card::e($task) . '">' . Card::e($name) . '</span>' . $state . '</li>';
            if($row['error'] !== '')
            {
                $html .= '<li class="tssggs-tasks__error">' . Card::e($this->translate('error') . ': ' . $row['error']) . '</li>';
            }
        }
        $html .= '</ul><p class="tssggs-label">' . Card::e($this->translate('gateway')) . '</p><ul class="tssggs-tasks">';
        foreach(GatewayHealth::check(isset($_GET['recheck'])) as $vendor => $row)
        {
            $state = $row['ok']
                ? '<span class="is-ok">' . Card::icon('check') . Card::e($this->translate('gateway_ok', ['ms' => $row['ms']])) . '</span>'
                : '<span class="is-bad" title="' . Card::e($row['error']) . '">' . Card::icon('close') . Card::e($this->translate('gateway_error')) . '</span>';
            $html .= '<li><span class="tssggs-tasks__name">' . Card::e(Helpers::vendorToDisplay($vendor)) . '</span>' . $state . '</li>';
            if(!$row['ok'])
            {
                $html .= '<li class="tssggs-tasks__error">' . Card::e($row['error']) . '</li>';
            }
        }
        $html .= '</ul><p class="tssggs-muted"><a href="addonmodules.php?module=TSSGGSModule&amp;recheck=1">' . Card::e($this->translate('gateway_recheck')) . '</a></p>';

        $html .= Card::link('addonmodules.php?module=TSSGGSModule&mg-page=settings&settings-page=cronSettings', $this->translate('review'), 'is-block');

        $this->body($html);
    }
}
