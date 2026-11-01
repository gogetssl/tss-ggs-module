<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\Card;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\CronStatus;

/**
 * System health: one row per scheduled task with its state; the error texts are listed under "Needs your attention"
 */
class SystemCheckWidget extends Card
{
    const ICONS = [
        'ProductPricingUpdate'      => 'tag-outline',
        'SSLCertificatesProcessing' => 'timer-sand',
        'SSLCertificates'           => 'sync',
        'RenewalNotifyCertificate'  => 'bell-outline',
        'SSLAutoInstall'            => 'web',
    ];

    public function loadHtml(): void
    {
        $latest = CronStatus::latest();
        $aside  = $latest !== null ? '<span class="tssggs-dcard__aside">' . Card::icon('clock-outline') . Card::e(self::when($latest)) . '</span>' : '';
        $this->start('pulse', $this->translate('title'), $aside, CronStatus::healthy() ? 'ok' : 'bad');

        $html = '';
        foreach(CronStatus::rows() as $task => $row)
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
                $state = Card::status('ok', self::when($row['last']), 'check', $row['last']);
            }
            // clear name of the task (the command name stays in the tooltip)
            $name  = $this->translate('name_' . $task);
            $name  = strpos($name, 'name_') !== false ? $task : $name;
            $html .= Card::row(self::ICONS[$task] ?? 'cog-outline', $name, '', $state);
        }
        $this->addElement(Card::html('<div class="tssggs-dcard__rows" id="tssggs-health">' . $html . '</div>'));

        $this->foot('addonmodules.php?module=TSSGGSModule&mg-page=settings&settings-page=cronSettings', 'cog-outline', $this->translate('review'));
    }

    /** Time only for today, otherwise the date */
    public static function when(string $datetime): string
    {
        $time = strtotime($datetime);

        return date('Y-m-d', $time) === date('Y-m-d') ? date('H:i', $time) : date('j M', $time);
    }
}
