<?php

namespace TSSGGSModule\App\Migration;

use TSSGGSModule\App\Libs\Certificates\AddonCertificates;
use TSSGGSModule\App\Libs\Products\ProductAddons;
use TSSGGSModule\App\Models\Request;
use WHMCS\Database\Capsule;

/**
 * Certificates the legacy module sold as WHMCS product addons (M-14). The legacy module kept them on the PARENT
 * service (tblsslorders.serviceid = hosting service, addon_id = the hosting addon), which the service migration never
 * reads. Here each of them gets its certificate service, as a new sale through the module would:
 *
 *  prepare  - for every legacy product addon whose product code matches a migrated product: per running hosting
 *             addon, a certificate service of that (legacy) product is created and its SSL order moved onto it
 *             (addon_id 0). The service migration then reads and moves it like any other service.
 *  switch   - the product addon is provisioned by the module from then on and linked to the product (its own
 *             prices are kept: no price sync for a migrated addon).
 *  rollback - the certificate services are deleted; the SSL orders, product addons and settings come back from the
 *             row backups.
 * Only the TheSSLStore module sold certificates as product addons; the GoGetSSL module never did.
 */
class AddonMigration
{
    /** Legacy server modules that provisioned product addons */
    const LEGACY_MODULES = ['tss' => 'thesslstorefullv2'];

    public static function legacyModule(string $source): ?string
    {
        return self::LEGACY_MODULES[$source] ?? null;
    }

    /** Product code a legacy product addon sells (its module setting configoption1), lower case */
    public static function addonCode(int $addonDefinitionId): string
    {
        return strtolower(trim((string)Capsule::table('tblmodule_configuration')->where('entity_type', 'addon')->where('entity_id', $addonDefinitionId)
            ->where('setting_name', 'configoption1')->value('value')));
    }

    /**
     * What the run would do (also shown before the start): per legacy product addon its code, the migrated product it
     * maps to and how many certificates move; certificates of addons without such a product stay on the legacy module
     *
     * @param array $productCodes migrated product id => legacy product code
     */
    public static function plan(string $source, array $productCodes): array
    {
        $module = self::legacyModule($source);
        if(!$module || !Capsule::schema()->hasColumn('tblsslorders', 'addon_id'))
        {
            return ['addons' => [], 'certificates' => 0, 'not_moved' => 0];
        }
        $byCode = [];
        foreach($productCodes as $pid => $code)
        {
            $code = strtolower(trim((string)$code));
            if($code !== '' && !isset($byCode[$code]))
            {
                $byCode[$code] = (int)$pid;
            }
        }
        $addons = [];
        $moved  = 0;
        $kept   = 0;
        foreach(Capsule::table('tbladdons')->where('module', $module)->get(['id', 'name']) as $def)
        {
            $code  = self::addonCode((int)$def->id);
            $pid   = $byCode[$code] ?? null;
            $certs = self::certificatesOf((int)$def->id, $module);
            $addons[(int)$def->id] = ['name' => (string)$def->name, 'code' => $code, 'pid' => $pid, 'certificates' => count($certs)];
            $pid ? $moved += count($certs) : $kept += count($certs);
        }

        return ['addons' => $addons, 'certificates' => $moved, 'not_moved' => $kept];
    }

    /**
     * SSL orders of a legacy product addon on hosting addons that still run: one per hosting addon - its newest order
     * (the legacy module keeps earlier orders of a renewed addon; they are history and stay where they are)
     */
    protected static function certificatesOf(int $addonDefinitionId, string $module): array
    {
        $out = [];
        $rows = Capsule::table('tblsslorders')
            ->join('tblhostingaddons', 'tblhostingaddons.id', '=', 'tblsslorders.addon_id')
            ->where('tblsslorders.module', $module)->where('tblsslorders.addon_id', '>', 0)
            ->where('tblhostingaddons.addonid', $addonDefinitionId)
            ->whereIn('tblhostingaddons.status', ['Pending', 'Active', 'Suspended'])
            ->orderBy('tblsslorders.id')
            ->get(['tblsslorders.id AS order_id', 'tblhostingaddons.id AS hosting_addon_id']);
        foreach($rows as $row)
        {
            $out[(int)$row->hosting_addon_id] = ['order_id' => (int)$row->order_id, 'hosting_addon_id' => (int)$row->hosting_addon_id];
        }

        return array_values($out);
    }

    /**
     * Before the service job list is made: certificate services for the legacy addon certificates.
     * Returns the summary the switch and the rollback use.
     */
    public static function prepare(int $runId, string $source, array $productCodes): array
    {
        $plan    = self::plan($source, $productCodes);
        $module  = (string)self::legacyModule($source);
        $summary = ['definitions' => [], 'services' => [], 'not_moved' => []];
        foreach($plan['addons'] as $definitionId => $addon)
        {
            if(!$addon['pid'])
            {
                if($addon['certificates'])
                {
                    $summary['not_moved'][] = ['addon' => $definitionId, 'name' => $addon['name'], 'code' => $addon['code'], 'certificates' => $addon['certificates']];
                }
                continue;
            }
            $summary['definitions'][$definitionId] = $addon['pid'];
            foreach(self::certificatesOf($definitionId, $module) as $cert)
            {
                $hostingAddon = Capsule::table('tblhostingaddons')->where('id', $cert['hosting_addon_id'])->first();
                $serviceId    = AddonCertificates::createService($hostingAddon, (int)$addon['pid'], (string)$hostingAddon->status, ['migration' => $runId]);
                $summary['services'][] = $serviceId;
                // saved at once: a failure further on still lets the rollback remove every service made so far
                Store::mergeSummary($runId, ['addons' => $summary]);
                Capsule::table('tblsslorders')->where('id', $cert['order_id'])->update(['serviceid' => $serviceId, 'addon_id' => 0]);
            }
        }

        return $summary;
    }

    /** In the switch transaction: the product addons are the module's from now on */
    public static function switchAddons(array $summary): void
    {
        foreach((array)($summary['definitions'] ?? []) as $definitionId => $productId)
        {
            Capsule::table('tbladdons')->where('id', (int)$definitionId)->update(['module' => 'TSSGGSModule', 'updated_at' => date('Y-m-d H:i:s')]);
            ProductAddons::link((int)$definitionId, (int)$productId, false);
        }
    }

    /** In the rollback transaction, before the row backups come back: the certificate services go */
    public static function rollback(array $summary): int
    {
        $ids = array_map('intval', (array)($summary['services'] ?? []));
        if(!$ids)
        {
            return 0;
        }
        Request::whereIn('serviceid', $ids)->delete();
        Capsule::table('tblhostingconfigoptions')->whereIn('relid', $ids)->delete();

        return Capsule::table('tblhosting')->whereIn('id', $ids)->delete();
    }
}
