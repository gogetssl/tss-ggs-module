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
        $this->setCss('tssggs-setup');

        $base  = 'addonmodules.php?module=TSSGGSModule&mg-page=';
        $steps = [
            [1, 'package-variant-closed', 'step_products', $base . 'products', (int)Capsule::table('tblproducts')->where('servertype', 'TSSGGSModule')->count() > 0],
            [2, 'tag-outline', 'step_pricing', $base . 'pricing', false],
            [3, 'sync', 'step_renewals', $base . 'settings&settings-page=sslSettings', false],
        ];

        // one strip: the lead, the three steps, when the guide disappears
        $html = '<span class="tssggs-setup__lead">' . Card::tile('format-list-checks', 'ok') . Card::e($this->translate('lead')) . '</span>';
        foreach($steps as [$number, $icon, $key, $url, $done])
        {
            $html .= '<a class="tssggs-setup__step' . ($done ? ' is-done' : '') . '" href="' . Card::e($url) . '">' . Card::tile($done ? 'check' : $icon, $done ? 'ok' : '')
                . '<span><b>' . Card::e($this->translate($key)) . '</b><small>' . Card::e($this->translate($key . '_detail')) . '</small></span>'
                . ($done ? '' : Card::icon('arrow-right')) . '</a>';
        }
        $html .= '<span class="tssggs-setup__note">' . Card::icon('clock-outline') . Card::e($this->translate('note_short', ['hours' => self::HOURS])) . '</span>';

        $this->addElement(Card::html($html));
    }
}
