<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\Card;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use WHMCS\Database\Capsule;

/**
 * "Finish setting up": three guided steps on the dashboard, removed automatically 72 hours after the dashboard was
 * first opened (the time of the first view is stored once).
 *
 * A step is done when its data exists (products created, a product priced) or when its page was saved (the Pricing
 * page, the SSL settings): those pages call markDone().
 */
class NextStepsWidget extends Container
{
    const HOURS   = 72;
    const SETTING = 'nextStepsStart';
    const DONE    = 'nextStepsDone';

    const STEP_PRICING  = 'pricing';
    const STEP_RENEWALS = 'renewals';

    protected static $done = null;

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

    /** Records that the admin completed a step (saved its page) */
    public static function markDone(string $step): void
    {
        try
        {
            $done = self::done();
            if(in_array($step, $done, true))
            {
                return;
            }
            $done[] = $step;
            Capsule::table('tbladdonmodules')->updateOrInsert(
                ['module' => 'configuration_TSSGGSModule', 'setting' => self::DONE],
                ['value' => implode(',', $done)]
            );
            self::$done = $done;
        }
        catch(\Throwable $e)
        {
            // the guide must never break a save
        }
    }

    protected static function done(): array
    {
        if(self::$done === null)
        {
            $value      = (string)Capsule::table('tbladdonmodules')->where('module', 'configuration_TSSGGSModule')->where('setting', self::DONE)->value('value');
            self::$done = $value === '' ? [] : explode(',', $value);
        }

        return self::$done;
    }

    /** At least one product of the module has a selling price */
    protected static function priced(): bool
    {
        return Capsule::table('tblpricing')
            ->join('tblproducts', 'tblproducts.id', '=', 'tblpricing.relid')
            ->where('tblpricing.type', 'product')
            ->where('tblproducts.servertype', 'TSSGGSModule')
            ->where(function($q) {
                foreach(['monthly', 'quarterly', 'semiannually', 'annually', 'biennially', 'triennially'] as $column)
                {
                    $q->orWhere('tblpricing.' . $column, '>', 0);
                }
            })
            ->exists();
    }

    public function loadHtml(): void
    {
        $this->setCss('tssggs-setup');

        $base  = 'addonmodules.php?module=TSSGGSModule&mg-page=';
        $done  = self::done();
        $steps = [
            [1, 'package-variant-closed', 'step_products', $base . 'products', (int)Capsule::table('tblproducts')->where('servertype', 'TSSGGSModule')->count() > 0],
            [2, 'tag-outline', 'step_pricing', $base . 'pricing', in_array(self::STEP_PRICING, $done, true) || self::priced()],
            [3, 'sync', 'step_renewals', $base . 'settings&settings-page=sslSettings', in_array(self::STEP_RENEWALS, $done, true)],
        ];

        // one strip: the lead, the three steps, when the guide disappears
        $html = '<span class="tssggs-setup__lead">' . Card::tile('format-list-checks', 'ok') . Card::e($this->translate('lead')) . '</span>';
        foreach($steps as [$number, $icon, $key, $url, $isDone])
        {
            $html .= '<a class="tssggs-setup__step' . ($isDone ? ' is-done' : '') . '" href="' . Card::e($url) . '">' . Card::tile($isDone ? 'check' : $icon, $isDone ? 'ok' : '')
                . '<span><b>' . Card::e($this->translate($key)) . '</b><small>' . Card::e($this->translate($key . '_detail')) . '</small></span>'
                . ($isDone ? '' : Card::icon('arrow-right')) . '</a>';
        }
        $html .= '<span class="tssggs-setup__note">' . Card::icon('clock-outline') . Card::e($this->translate('note_short', ['hours' => self::HOURS])) . '</span>';

        $this->addElement(Card::html($html));
    }
}
