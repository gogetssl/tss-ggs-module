<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Models\Whmcs\AddonModule;

/**
 * Per-product settings of the Products page (stored in tbladdonmodules as "product_<pid>"):
 *  - months:          certificate term ordered at the gateway regardless of the billing cycle (0 = from the billing cycle)
 *  - margin:          profit margin % of the automatic price update for this product ('' = the global margin)
 *  - processingGuide / issuedMessage: client messages overriding the General settings ('' = the global text)
 */
class ProductSettings
{
    const DEFAULTS = ['months' => 0, 'margin' => '', 'processingGuide' => '', 'issuedMessage' => ''];
    const MONTHS   = [0, 12, 24, 36, 48, 60];

    protected static $cache = [];

    public static function get($productId)
    {
        $productId = (int)$productId;
        if(!isset(self::$cache[$productId]))
        {
            $value  = AddonModule::where('module', 'configuration_TSSGGSModule')->where('setting', 'product_' . $productId)->value('value');
            $stored = $value ? (json_decode($value, true) ?: []) : [];
            // installs before 1.3.0 kept the product margin under its old name
            if(!isset($stored['margin']) && isset($stored['commission']))
            {
                $stored['margin'] = $stored['commission'];
            }
            self::$cache[$productId] = array_merge(self::DEFAULTS, array_intersect_key($stored, self::DEFAULTS));
        }

        return self::$cache[$productId];
    }

    public static function save($productId, array $data)
    {
        $data = array_merge(self::DEFAULTS, array_intersect_key($data, self::DEFAULTS));

        $data['months']     = in_array((int)$data['months'], self::MONTHS, true) ? (int)$data['months'] : 0;
        $data['margin']     = trim((string)$data['margin']) === '' ? '' : (string)(float)$data['margin'];

        AddonModule::updateOrInsert(
            ['module' => 'configuration_TSSGGSModule', 'setting' => 'product_' . (int)$productId],
            ['value' => json_encode($data)]
        );
        unset(self::$cache[(int)$productId]);
    }

    public static function margin($productId, $globalMargin)
    {
        $margin = self::get($productId)['margin'];

        return $margin === '' ? (float)$globalMargin : (float)$margin;
    }

    /**
     * Product message, or the General settings one when the product has none
     */
    public static function message($productId, $key)
    {
        $text = trim((string)self::get($productId)[$key]);

        return $text !== '' ? $text : trim((string)GeneralSettings::get($key));
    }
}
