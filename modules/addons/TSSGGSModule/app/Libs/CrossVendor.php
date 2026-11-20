<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use WHMCS\Database\Capsule;

/**
 * The second vendor an edition can connect (Settings > API, "Connect ... products").
 *
 * Both vendors sell mostly the same certificates, so a connected vendor offers only what the edition's own vendor does
 * not sell (Vlad, 2026-11-20) - a reseller can never import the same RapidSSL / PositiveSSL twice:
 *  - GoGetSSL connected to TheSSLStore Plugin: GoGetSSL-brand DV / OV / EV certificates, GoGetSSL-brand code signing,
 *    LEI codes
 *  - TheSSLStore connected to the GoGetSSL Module: SiteLock, cWatch, CodeGuard
 * Everything else of the connected vendor stays hidden. Legacy migration never looks at the connected vendor.
 */
class CrossVendor
{
    /** vendor => ['brands' => [brand => categories], 'categories' => [categories of any brand]] */
    const OFFERED = [
        Edition::GGS => ['brands' => ['gogetssl' => ['tls', 'cs']], 'categories' => ['lei']],
        Edition::TSS => ['brands' => [], 'categories' => ['site-lock', 'c-watch', 'code-guard']],
    ];

    /**
     * Whether a catalogue product may be offered: always for the edition's own vendor; for the connected vendor only its
     * exclusive products; never for a vendor that is not connected
     */
    public static function offered($remoteProduct)
    {
        $vendor = strtolower((string)$remoteProduct->vendor);
        if($vendor === Edition::primaryVendor())
        {
            return true;
        }
        if(!Edition::isConnected($vendor))
        {
            return false;
        }

        return self::exclusive($vendor, (string)$remoteProduct->category, (string)$remoteProduct->brand);
    }

    /** Whether the vendor's product of this category and brand is on its exclusive list */
    public static function exclusive($vendor, $category, $brand)
    {
        $rules    = self::OFFERED[strtolower((string)$vendor)] ?? null;
        $category = strtolower(trim((string)$category));
        $brand    = strtolower(trim((string)$brand));
        if(!$rules)
        {
            return false;
        }

        return in_array($category, $rules['categories'], true) || in_array($category, $rules['brands'][$brand] ?? [], true);
    }

    /** Vendor key ('ggs' / 'tss') of a WHMCS service's product, the edition's own vendor when unknown */
    public static function vendorOfService($serviceId)
    {
        $vendor = Capsule::table('tblhosting')->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->where('tblhosting.id', (int)$serviceId)->value('tblproducts.configoption1');

        return strtolower((string)$vendor) ?: Edition::primaryVendor();
    }

    /** 'GoGetSSL' / 'TheSSLStore': the vendor that sells the service's product */
    public static function brandOfService($serviceId)
    {
        return Edition::brandOf(self::vendorOfService($serviceId));
    }

    /**
     * WHMCS products of the vendor (not retired): a connected vendor can not be disconnected while it sells some
     *
     * @return int
     */
    public static function productCount($vendor)
    {
        return (int)Capsule::table('tblproducts')->where('servertype', 'TSSGGSModule')
            ->where('configoption1', strtoupper((string)$vendor))->where('retired', 0)->count();
    }
}
