<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\Card;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\CronStatus;

/**
 * System health: one row per scheduled task with its state; the error texts are listed under "Needs your attention"
 */
class SystemCheckWidget extends Card
{
    /** tasks not listed in this panel */
    const HIDDEN = ['SSLCertificatesProcessing'];

    const ICONS = [
        'ProductPricingUpdate'      => 'tag-outline',
        'SSLCertificatesProcessing' => 'timer-sand',
        'SSLCertificates'           => 'sync',
        'RenewalNotifyCertificate'  => 'bell-outline',
        'SSLAutoInstall'            => 'web',
    ];

    public function loadHtml(): void
    {
        // the pending orders check runs every 5 minutes and is not listed here (its failures show under "Needs your attention")
        $rows = array_diff_key(CronStatus::rows(), array_flip(self::HIDDEN));

        $latest = null;
        $ok     = true;
        foreach($rows as $row)
        {
            $ok     = $ok && $row['last'] !== null && $row['error'] === '';
            $latest = $row['last'] !== null && ($latest === null || strcmp($row['last'], $latest) > 0) ? $row['last'] : $latest;
        }
        $aside = $latest !== null ? '<span class="tssggs-dcard__aside">' . Card::icon('clock-outline') . Card::e($this->when($latest)) . '</span>' : '';
        $this->start('pulse', $this->translate('title'), $aside, $ok ? 'ok' : 'bad');

        $html = '';
        foreach($rows as $task => $row)
        {
            if($row['last'] === null)
            {
                $state = Card::status('bad', $this->translate('never'), 'close');
            }
            elseif($row['error'] !== '')
            {
                $state = Card::status('bad', $this->translate('failing'), 'alert-outline', $row['error']);
            }
            else
            {
                $state = Card::status('ok', $this->when($row['last']), 'check', $row['last']);
            }
            // clear name of the task (the command name stays in the tooltip)
            $name  = $this->translate('name_' . $task);
            $name  = strpos($name, 'name_') !== false ? $task : $name;
            $html .= Card::row(self::ICONS[$task] ?? 'cog-outline', $name, '', $state);
        }
        $this->addElement(Card::html('<div class="tssggs-dcard__rows" id="tssggs-health">' . $html . '</div>'));

        $this->foot('addonmodules.php?module=TSSGGSModule&mg-page=settings&settings-page=cronSettings', 'cog-outline', $this->translate('review'));
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
            return $this->translate('yesterday', ['time' => date('H:i', $time)]);
        }

        return date(date('Y', $time) === date('Y') ? 'j M H:i' : 'j M Y H:i', $time);
    }
}
