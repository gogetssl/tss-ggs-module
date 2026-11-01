<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\Card;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use WHMCS\Database\Capsule;

/**
 * "Next steps after module installation": three guided steps on the dashboard, removed automatically 72 hours after
 * the dashboard was first opened (the time of the first view is stored once).
 */
class NextStepsWidget extends Container
{
    const HOURS   = 72;
    const SETTING = 'nextStepsStart';

    /**
     * Whether the guide is still shown; the first call starts the 72 hours
     */
    public static function visible(): bool
    {
        $where = ['module' => 'configuration_TSSGGSModule', 'setting' => self::SETTING];
        $start = (int)Capsule::table('tbladdonmodules')->where($where)->value('value');
        if($start <= 0)
        {
            $start = time();
            Capsule::table('tbladdonmodules')->updateOrInsert($where, ['value' => (string)$start]);
        }

        return time() < $start + self::HOURS * 3600;
    }

    public function loadHtml(): void
    {
        $this->setCss('tssggs-steps');

        $base  = 'addonmodules.php?module=TSSGGSModule&mg-page=';
        $steps = [
            [1, 'layers-outline', 'step_products', $base . 'products', (int)Capsule::table('tblproducts')->where('servertype', 'TSSGGSModule')->count() > 0],
            [2, 'cash', 'step_pricing', $base . 'pricing', false],
            [3, 'autorenew', 'step_renewals', $base . 'settings&settings-page=sslSettings', false],
        ];

        $html = '<div class="tssggs-steps__head"><h3><span class="tssggs-steps__badge">' . Card::icon('cog-outline') . '</span>' . Card::e($this->translate('title')) . '</h3>'
            . '<span class="tssggs-steps__chip">' . Card::e($this->translate('chip')) . '</span></div><div class="tssggs-steps__grid">';
        foreach($steps as [$number, $icon, $key, $url, $done])
        {
            $html .= '<a class="tssggs-step' . ($done ? ' is-done' : '') . '" href="' . Card::e($url) . '"><span class="tssggs-step__num">' . ($done ? Card::icon('check') : $number) . '</span>'
                . '<span class="tssggs-step__text"><strong>' . Card::icon($icon) . Card::e($this->translate($key)) . '</strong><small>' . Card::e($this->translate($key . '_detail')) . '</small></span>'
                . Card::icon('arrow-right') . '</a>';
        }
        $html .= '</div><p class="tssggs-steps__note">' . Card::icon('information-outline') . Card::e($this->translate('note', ['hours' => self::HOURS])) . '</p>';

        $this->addElement(Card::html($html));
    }
}
