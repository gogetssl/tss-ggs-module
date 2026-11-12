<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\Card;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use WHMCS\Database\Capsule;

/**
 * "Finish setting up": three guided steps on the dashboard, shown until all three are done.
 *
 * A step is done when its data exists (products created, a product priced) or when its page was saved (the Pricing
 * page, the SSL settings): those pages call markDone().
 */
class NextStepsWidget extends Container
{
    const DONE = 'nextStepsDone';

    const STEP_PRICING  = 'pricing';
    const STEP_RENEWALS = 'renewals';

    protected static $done = null;

    /** Whether the guide is still shown: until all three steps are done */
    public static function visible(): bool
    {
        try
        {
            return in_array(false, array_column(self::steps(), 4), true);
        }
        catch(\Throwable $e)
        {
            return false;
        }
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

    /** [number, icon, text key, url, done] */
    protected static function steps(): array
    {
        $base = 'addonmodules.php?module=TSSGGSModule&mg-page=';
        $done = self::done();

        return [
            [1, 'package-variant-closed', 'step_products', $base . 'products', (int)Capsule::table('tblproducts')->where('servertype', 'TSSGGSModule')->count() > 0],
            [2, 'tag-outline', 'step_pricing', $base . 'pricing', in_array(self::STEP_PRICING, $done, true) || self::priced()],
            [3, 'sync', 'step_renewals', $base . 'settings&settings-page=sslSettings', in_array(self::STEP_RENEWALS, $done, true)],
        ];
    }

    public function loadHtml(): void
    {
        $this->setCss('tssggs-setup');

        // one strip: the lead and the three steps
        $html = '<span class="tssggs-setup__lead">' . Card::tile('format-list-checks', 'ok') . Card::e($this->translate('lead')) . '</span>';
        foreach(self::steps() as [$number, $icon, $key, $url, $isDone])
        {
            $html .= '<a class="tssggs-setup__step' . ($isDone ? ' is-done' : '') . '" href="' . Card::e($url) . '">' . Card::tile($isDone ? 'check' : $icon, $isDone ? 'ok' : '')
                . '<span><b>' . Card::e($this->translate($key)) . '</b><small>' . Card::e($this->translate($key . '_detail')) . '</small></span>'
                . ($isDone ? '' : Card::icon('arrow-right')) . '</a>';
        }

        $this->addElement(Card::html($html));
    }
}
