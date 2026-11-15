<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use WHMCS\Database\Capsule;

/**
 * Certificates sold as WHMCS product addons (M-14), the catalogue side: which certificate product a product addon
 * stands for, creating such an addon from the Products page, and keeping its prices with the product's.
 *
 * The link is the module setting "addon_<tbladdons.id>" = {"pid": <certificate product>}. The addon's own WHMCS
 * module settings are not used: the module's products are configured from the addon module only.
 */
class ProductAddons
{
    const SETTING_PREFIX = 'addon_';

    protected static function settingsModule(): string
    {
        return 'configuration_TSSGGSModule';
    }

    /** The certificate product of a product addon (tbladdons.id), null when it is not one of the module's */
    public static function productOf(int $addonDefinitionId): ?int
    {
        $value = Capsule::table('tbladdonmodules')->where('module', self::settingsModule())->where('setting', self::SETTING_PREFIX . $addonDefinitionId)->value('value');
        $pid   = (int)((json_decode((string)$value, true) ?: [])['pid'] ?? 0);

        return $pid > 0 && Capsule::table('tblproducts')->where('id', $pid)->where('servertype', 'TSSGGSModule')->exists() ? $pid : null;
    }

    /** The product addons (tbladdons.id) that sell a certificate product */
    public static function addonsOf(int $productId): array
    {
        $ids = [];
        foreach(Capsule::table('tbladdonmodules')->where('module', self::settingsModule())->where('setting', 'like', self::SETTING_PREFIX . '%')->get(['setting', 'value']) as $row)
        {
            $addonId = (int)substr((string)$row->setting, strlen(self::SETTING_PREFIX));
            if($addonId > 0 && (int)((json_decode((string)$row->value, true) ?: [])['pid'] ?? 0) === $productId
                && Capsule::table('tbladdons')->where('id', $addonId)->where('module', 'TSSGGSModule')->exists())
            {
                $ids[] = $addonId;
            }
        }

        return $ids;
    }

    public static function link(int $addonDefinitionId, int $productId): void
    {
        $setting = self::SETTING_PREFIX . $addonDefinitionId;
        $value   = json_encode(['pid' => $productId]);
        if(Capsule::table('tbladdonmodules')->where('module', self::settingsModule())->where('setting', $setting)->exists())
        {
            Capsule::table('tbladdonmodules')->where('module', self::settingsModule())->where('setting', $setting)->update(['value' => $value]);
        }
        else
        {
            Capsule::table('tbladdonmodules')->insert(['module' => self::settingsModule(), 'setting' => $setting, 'value' => $value]);
        }
    }

    public static function unlink(int $addonDefinitionId): void
    {
        Capsule::table('tbladdonmodules')->where('module', self::settingsModule())->where('setting', self::SETTING_PREFIX . $addonDefinitionId)->delete();
    }

    /**
     * Creates a WHMCS product addon that sells the certificate product: same name and description, same billing type
     * and prices, provisioned by this module, paid before it is set up. It is offered with no product yet - the admin
     * picks the products it goes with in WHMCS (Products/Services > Product Addons). Returns the addon id.
     */
    public static function create(int $productId): int
    {
        $product = Capsule::table('tblproducts')->where('id', $productId)->where('servertype', 'TSSGGSModule')->first();
        if(!$product)
        {
            throw new \RuntimeException('Product not found');
        }
        $now     = date('Y-m-d H:i:s');
        $addonId = (int)Capsule::table('tbladdons')->insertGetId([
            'packages'       => '',
            'name'           => (string)$product->name,
            'description'    => (string)$product->description,
            'billingcycle'   => in_array($product->paytype, ['free', 'onetime', 'recurring'], true) ? $product->paytype : 'recurring',
            'allowqty'       => 0,
            'prorate'        => 0,
            'tax'            => (int)$product->tax,
            'showorder'      => 1,
            'hidden'         => 0,
            'retired'        => 0,
            'downloads'      => '',
            'autoactivate'   => 'payment',
            'suspendproduct' => 0,
            'welcomeemail'   => 0,
            'type'           => 'other',
            'module'         => 'TSSGGSModule',
            'server_group_id'=> 0,
            'weight'         => 0,
            'autolinkby'     => '',
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);
        self::link($addonId, $productId);
        self::copyPricing($productId, $addonId);

        return $addonId;
    }

    /** The product's prices become the addon's (every currency and billing cycle) */
    public static function copyPricing(int $productId, int $addonId): void
    {
        $columns = ['msetupfee', 'qsetupfee', 'ssetupfee', 'asetupfee', 'bsetupfee', 'tsetupfee', 'monthly', 'quarterly', 'semiannually', 'annually', 'biennially', 'triennially'];
        foreach(Capsule::table('tblpricing')->where('type', 'product')->where('relid', $productId)->get() as $row)
        {
            $values = [];
            foreach($columns as $column)
            {
                $values[$column] = $row->$column;
            }
            $match = ['type' => 'addon', 'relid' => $addonId, 'currency' => $row->currency];
            if(Capsule::table('tblpricing')->where($match)->exists())
            {
                Capsule::table('tblpricing')->where($match)->update($values);
            }
            else
            {
                Capsule::table('tblpricing')->insert($match + $values);
            }
        }
    }

    /** Every linked product addon takes its product's current prices; returns how many were updated */
    public static function syncAll(): int
    {
        $count = 0;
        foreach(Capsule::table('tbladdonmodules')->where('module', self::settingsModule())->where('setting', 'like', self::SETTING_PREFIX . '%')->pluck('setting') as $setting)
        {
            $addonId = (int)substr((string)$setting, strlen(self::SETTING_PREFIX));
            $pid     = $addonId > 0 && Capsule::table('tbladdons')->where('id', $addonId)->where('module', 'TSSGGSModule')->exists() ? self::productOf($addonId) : null;
            if($pid)
            {
                self::copyPricing($pid, $addonId);
                $count++;
            }
        }

        return $count;
    }

    /** After the pricing update changed a product: its product addons follow (they sell the same certificate) */
    public static function syncPricing(int $productId): int
    {
        $addons = self::addonsOf($productId);
        foreach($addons as $addonId)
        {
            self::copyPricing($productId, $addonId);
        }

        return count($addons);
    }
}
