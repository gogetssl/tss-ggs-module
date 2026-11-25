<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Products;

use ModulesGarden\TSSGGSModule\App\Libs\Support\Edition;
use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;

/**
 * The vendor products the Products page lists, and the two filters of the list (imported or not, validation level).
 */
class CatalogueList
{
    /** the "show" filter: '' (all), 'imported' or 'not_imported' */
    const SHOW = ['imported', 'not_imported'];

    /** the validation filter: '' (all), 'dv', 'ov', 'ev' or 'other' (no validation level: code signing, S/MIME, LEI...) */
    const TYPES = ['dv', 'ov', 'ev', 'other'];

    /** brands listed first; the others follow by name */
    const BRAND_ORDER = ['Digicert' => 1, 'RapidSSL' => 2, 'GeoTrust' => 3, 'Sectigo' => 99];

    /**
     * @return array [[remote product, WHMCS product or null], ...] in the display order - the edition's own vendors;
     *               a product the module can not sell yet only when it was imported earlier
     */
    public static function catalogue(): array
    {
        if(!RemoteProduct::isSynchronized())
        {
            RemoteProduct::synchronize();
        }

        // only the edition's own vendors (a catalogue copied from the other edition is not offered)
        $vendors        = array_map('strtoupper', Edition::vendors());
        $remoteProducts = RemoteProduct::whereIn('vendor', $vendors)->get()->sortBy(function($product) {
            return (self::BRAND_ORDER[$product->brand] ?? 50) . $product->name;
        });

        $list = [];
        foreach($remoteProducts as $remoteProduct)
        {
            $whmcsProduct = $remoteProduct->getWhmcsProduct();
            if(!$whmcsProduct && !ProductCatalog::importable($remoteProduct))
            {
                continue;
            }
            $list[] = [$remoteProduct, $whmcsProduct];
        }

        return $list;
    }

    /** dv / ov / ev / other for a remote product */
    public static function typeOf($remoteProduct): string
    {
        $validation = strtolower(trim((string)$remoteProduct->validation));

        return in_array($validation, ['dv', 'ov', 'ev'], true) ? $validation : 'other';
    }

    /** true when a product passes the show / type filters ('' = no filter) */
    public static function passes($remoteProduct, $whmcsProduct, string $show, string $type): bool
    {
        if(($show === 'imported' && !$whmcsProduct) || ($show === 'not_imported' && $whmcsProduct))
        {
            return false;
        }

        return $type === '' || self::typeOf($remoteProduct) === $type;
    }
}
