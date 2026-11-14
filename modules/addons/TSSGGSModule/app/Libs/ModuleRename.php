<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use WHMCS\Database\Capsule;

/**
 * Up to 1.3.0 the module was installed in a folder named after neither product (tssmodule, ggsmodule).
 * From 1.4.0 it carries its own name - TSSPlugin for TheSSLStore Plugin, GGSModule for the GoGetSSL Module.
 *
 * WHMCS knows a module by that folder name and keeps it in its own records, so an install upgraded from an
 * earlier version has them moved over once: the server type of every product the module provisions, and the
 * addon's settings. Without it the products would point at a module that is no longer there.
 *
 * It runs on activation, on upgrade and on the first cron, and does nothing once there is nothing left to move.
 */
class ModuleRename
{
    /** Settings WHMCS itself writes when the addon is activated: whatever the admin just chose wins */
    const KEEP_NEW = ['access'];

    /**
     * @return array what was moved, for the log: ["tblproducts: 7", ...]
     */
    public static function migrate(): array
    {
        $new  = Edition::folder();
        $done = [];

        foreach(Edition::legacyFolders() as $old)
        {
            if($old === $new)
            {
                continue;
            }

            // MySQL compares these columns without regard to case, so a name that differs from the old one only
            // in case is already the same name to the database: the rows are found as they are and only their
            // spelling is brought up to date. Nothing may be deleted in that case - it would delete the rows in use.
            $sameToTheDatabase = strcasecmp($old, $new) === 0;

            $done = array_merge(
                $done,
                self::products($old, $new),
                self::addonSettings($old, $new, $sameToTheDatabase),
                self::servers($old, $new)
            );
        }

        return $done;
    }

    /**
     * Products the module provisions: WHMCS reads the module's name from their server type
     */
    protected static function products($old, $new): array
    {
        try
        {
            $count = Capsule::table('tblproducts')->where('servertype', $old)->update(['servertype' => $new]);

            return $count ? ['tblproducts.servertype: ' . $count] : [];
        }
        catch(\Throwable $e)
        {
            return [];
        }
    }

    /**
     * The addon's settings, and the module's own settings namespace ("configuration_<module>")
     */
    protected static function addonSettings($old, $new, $sameToTheDatabase = false): array
    {
        $done = [];
        foreach([$old => $new, 'configuration_' . $old => 'configuration_' . $new] as $from => $to)
        {
            try
            {
                if($sameToTheDatabase)
                {
                    $moved = Capsule::table('tbladdonmodules')->where('module', $from)->update(['module' => $to]);
                    if($moved)
                    {
                        $done[] = 'tbladdonmodules ' . $from . ' -> ' . $to . ': ' . $moved;
                    }
                    continue;
                }

                $moved = 0;
                foreach(Capsule::table('tbladdonmodules')->where('module', $from)->get(['setting']) as $row)
                {
                    if(in_array($row->setting, self::KEEP_NEW, true) && Capsule::table('tbladdonmodules')->where('module', $to)->where('setting', $row->setting)->exists())
                    {
                        Capsule::table('tbladdonmodules')->where('module', $from)->where('setting', $row->setting)->delete();
                        continue;
                    }

                    // a setting the activation has just created with its default makes way for the one in use
                    Capsule::table('tbladdonmodules')->where('module', $to)->where('setting', $row->setting)->delete();
                    Capsule::table('tbladdonmodules')->where('module', $from)->where('setting', $row->setting)->update(['module' => $to]);
                    $moved++;
                }
                if($moved)
                {
                    $done[] = 'tbladdonmodules ' . $from . ' -> ' . $to . ': ' . $moved;
                }
            }
            catch(\Throwable $e)
            {
                // leave what cannot be moved; the next run tries again
            }
        }

        return $done;
    }

    /**
     * A server entry of this module's type, where the admin made one
     */
    protected static function servers($old, $new): array
    {
        try
        {
            if(!Capsule::schema()->hasTable('tblservers'))
            {
                return [];
            }
            $count = Capsule::table('tblservers')->where('type', $old)->update(['type' => $new]);

            return $count ? ['tblservers.type: ' . $count] : [];
        }
        catch(\Throwable $e)
        {
            return [];
        }
    }
}
