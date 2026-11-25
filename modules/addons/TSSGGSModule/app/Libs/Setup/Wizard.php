<?php

namespace TSSGGSModule\App\Libs\Setup;

use TSSGGSModule\App\Libs\Support\Edition;
use TSSGGSModule\App\Migration\Choice;
use TSSGGSModule\App\Migration\Detector;
use TSSGGSModule\App\Migration\Store;
use WHMCS\Database\Capsule;

/**
 * The order of the setup wizard's pages (App\Pages\Admin\Configuration).
 *
 * Page 3 (legacy modules) is part of the wizard only when a legacy module is installed; without one the wizard has
 * five steps and the numbering adjusts. Page 7 (the migration itself) comes between the products and the
 * finalization, and only when "migrate" was chosen on page 3.
 */
class Wizard
{
    /** every page the wizard can have */
    const PAGES = [1, 2, 3, 4, 5, 6, 7];

    protected static $hasLegacy = null;

    public static function hasLegacy(): bool
    {
        if(self::$hasLegacy === null)
        {
            try
            {
                self::$hasLegacy = Detector::anyFound();
            }
            catch(\Throwable $e)
            {
                self::$hasLegacy = false;
            }
        }

        return self::$hasLegacy;
    }

    /** @return int[] the wizard's pages in their order: [1, 2, (3,) 4, 5, (7,) 6] */
    public static function pages(): array
    {
        if(!self::hasLegacy())
        {
            return [1, 2, 4, 5, 6];
        }

        return self::migrationPlanned() ? [1, 2, 3, 4, 5, 7, 6] : [1, 2, 3, 4, 5, 6];
    }

    /** "Migrate" was chosen for this edition's legacy module */
    public static function migrationPlanned(): bool
    {
        try
        {
            return Choice::get(Edition::get()) === Choice::MIGRATE;
        }
        catch(\Throwable $e)
        {
            return false;
        }
    }

    /** A migration of this edition's legacy module has completed */
    public static function migrationCompleted(): bool
    {
        try
        {
            Store::createTables();

            return Capsule::table(Store::runsTable())->where('source', Edition::get())->where('status', Store::RUN_COMPLETED)->exists();
        }
        catch(\Throwable $e)
        {
            return false;
        }
    }

    public static function nextPage(int $page): int
    {
        $pages = self::pages();
        $i     = array_search($page, $pages, true);

        return $i !== false && isset($pages[$i + 1]) ? $pages[$i + 1] : $page;
    }

    /** @return int the position of a page in the wizard, from 0 */
    public static function position(int $page): int
    {
        return (int)array_search($page, self::pages(), true);
    }

    /** @return int how far the wizard is on this page, 0 - 100 */
    public static function percent(int $page): int
    {
        $pages = self::pages();

        return count($pages) > 1 ? (int)round(self::position($page) / (count($pages) - 1) * 100) : 100;
    }
}
