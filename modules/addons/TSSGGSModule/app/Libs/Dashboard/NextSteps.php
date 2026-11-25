<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Dashboard;

use ModulesGarden\TSSGGSModule\App\Kernel\Kernel;
use ModulesGarden\TSSGGSModule\App\Kernel\Page;
use WHMCS\Database\Capsule;

/**
 * "Finish setting up": the three guided steps on the dashboard, shown until all three are done.
 *
 * A step is done when its data exists (products created, a product priced) or when its page was saved (the Pricing
 * page, the SSL settings): those pages call markDone().
 */
class NextSteps
{
    const DONE          = 'nextStepsDone';
    const STEP_PRICING  = 'pricing';
    const STEP_RENEWALS = 'renewals';

    protected static $done = null;

    /** Whether the guide is still shown: until all three steps are done */
    public static function visible(): bool
    {
        try
        {
            return in_array(false, array_column(self::steps(), 'done'), true);
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
                ['module' => 'configuration_' . Kernel::module(), 'setting' => self::DONE],
                ['value' => implode(',', $done)]
            );
            self::$done = $done;
        }
        catch(\Throwable $e)
        {
            // the guide must never break a save
        }
    }

    /** @return array[] number, icon, key (of the step's texts), url, done */
    public static function steps(): array
    {
        $done = self::done();

        return [
            ['number' => 1, 'icon' => 'package-variant-closed', 'key' => 'step_products', 'url' => Page::pageUrl('products'),
             'done'   => (int)Capsule::table('tblproducts')->where('servertype', Kernel::module())->count() > 0],
            ['number' => 2, 'icon' => 'tag-outline', 'key' => 'step_pricing', 'url' => Page::pageUrl('pricing'),
             'done'   => in_array(self::STEP_PRICING, $done, true) || self::priced()],
            ['number' => 3, 'icon' => 'sync', 'key' => 'step_renewals', 'url' => Page::pageUrl('settings', 'index', ['settings-page' => 'sslSettings']),
             'done'   => in_array(self::STEP_RENEWALS, $done, true)],
        ];
    }

    protected static function done(): array
    {
        if(self::$done === null)
        {
            $value      = (string)Capsule::table('tbladdonmodules')->where('module', 'configuration_' . Kernel::module())->where('setting', self::DONE)->value('value');
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
            ->where('tblproducts.servertype', Kernel::module())
            ->where(function($q) {
                foreach(['monthly', 'quarterly', 'semiannually', 'annually', 'biennially', 'triennially'] as $column)
                {
                    $q->orWhere('tblpricing.' . $column, '>', 0);
                }
            })
            ->exists();
    }
}
