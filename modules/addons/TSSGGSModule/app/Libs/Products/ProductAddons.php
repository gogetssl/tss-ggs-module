<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Products;

use WHMCS\Database\Capsule;
use ModulesGarden\TSSGGSModule\App\Libs\ProductLines\GatewayOrderService;

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

    /** @param bool $syncPrices the addon takes the product's prices (created here); a migrated addon keeps its own */
    public static function link(int $addonDefinitionId, int $productId, bool $syncPrices = true): void
    {
        $setting = self::SETTING_PREFIX . $addonDefinitionId;
        $value   = json_encode(['pid' => $productId, 'sync' => $syncPrices]);
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

    const PRICE_COLUMNS = ['msetupfee', 'qsetupfee', 'ssetupfee', 'asetupfee', 'bsetupfee', 'tsetupfee', 'monthly', 'quarterly', 'semiannually', 'annually', 'biennially', 'triennially'];

    /** The product's prices become the addon's (every currency and billing cycle); the link remembers what was copied */
    public static function copyPricing(int $productId, int $addonId): void
    {
        foreach(Capsule::table('tblpricing')->where('type', 'product')->where('relid', $productId)->get() as $row)
        {
            $values = [];
            foreach(self::PRICE_COLUMNS as $column)
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
        self::setLinkValue($addonId, 'prices', self::fingerprint('addon', $addonId));
    }

    /** The prices of a product or addon as one comparable value */
    protected static function fingerprint(string $type, int $id): string
    {
        $rows = [];
        foreach(Capsule::table('tblpricing')->where('type', $type)->where('relid', $id)->orderBy('currency')->get() as $row)
        {
            $rows[] = $row->currency . ':' . implode(',', array_map(function($c) use ($row) { return number_format((float)$row->$c, 2, '.', ''); }, self::PRICE_COLUMNS));
        }

        return md5(implode('|', $rows));
    }

    protected static function linkValue(int $addonId): array
    {
        return json_decode((string)Capsule::table('tbladdonmodules')->where('module', self::settingsModule())->where('setting', self::SETTING_PREFIX . $addonId)->value('value'), true) ?: [];
    }

    protected static function setLinkValue(int $addonId, string $key, $value): void
    {
        $data = self::linkValue($addonId);
        if(!$data)
        {
            return;
        }
        $data[$key] = $value;
        Capsule::table('tbladdonmodules')->where('module', self::settingsModule())->where('setting', self::SETTING_PREFIX . $addonId)->update(['value' => json_encode($data)]);
    }

    /**
     * Every linked product addon takes its product's current prices, unless the administrator set its prices by hand
     * (they differ from the last copy): that addon then keeps its own prices from now on. Returns how many changed.
     */
    public static function syncAll(): int
    {
        $count = 0;
        foreach(Capsule::table('tbladdonmodules')->where('module', self::settingsModule())->where('setting', 'like', self::SETTING_PREFIX . '%')->get(['setting', 'value']) as $row)
        {
            $addonId = (int)substr((string)$row->setting, strlen(self::SETTING_PREFIX));
            $data    = json_decode((string)$row->value, true) ?: [];
            if(($data['sync'] ?? true) === false)
            {
                continue; // taken over from the legacy module, or priced by hand
            }
            $pid = $addonId > 0 && Capsule::table('tbladdons')->where('id', $addonId)->where('module', 'TSSGGSModule')->exists() ? self::productOf($addonId) : null;
            if(!$pid)
            {
                continue;
            }
            if(!empty($data['prices']) && $data['prices'] !== self::fingerprint('addon', $addonId))
            {
                self::setLinkValue($addonId, 'sync', false);
                \logActivity('Product addon #' . $addonId . ': its prices were changed by hand, they no longer follow the product #' . $pid);
                continue;
            }
            $before = self::fingerprint('addon', $addonId);
            self::copyPricing($pid, $addonId);
            if(self::fingerprint('addon', $addonId) !== $before)
            {
                $count++;
            }
        }

        return $count;
    }

    /** Categories that work as product addons: no subscriptions, no CodeGuard, no code signing (token delivery options) */
    const ADDON_CATEGORIES = ['tls', 'smime', 'ds'];

    /** Why a product can not be sold as a product addon ('' when it can) */
    public static function refusal(int $productId): string
    {
        $product = Capsule::table('tblproducts')->where('id', $productId)->first(['paytype', 'configoption1', 'configoption2']);
        if(!$product)
        {
            return 'Product not found';
        }
        if($product->paytype === 'free')
        {
            return 'A free product can not be sold as a product addon';
        }
        $remote   = GatewayOrderService::remoteProduct($productId);
        $raw      = $remote && is_array($remote->rawData) ? $remote->rawData : [];
        $category = strtolower((string)($raw['category'] ?? 'tls'));
        if(!in_array($category, self::ADDON_CATEGORIES, true))
        {
            return 'This product type can not be sold as a product addon (subscriptions, CodeGuard and code signing need options and billing an addon does not have)';
        }

        return '';
    }

    /** The product was hidden / shown / retired: the product addons that sell it follow */
    public static function followProduct(int $productId, array $change): void
    {
        $values = array_intersect_key($change, ['hidden' => 1, 'retired' => 1]);
        $ids    = self::addonsOf($productId);
        if($values && $ids)
        {
            Capsule::table('tbladdons')->whereIn('id', $ids)->update($values + ['updated_at' => date('Y-m-d H:i:s')]);
        }
    }
}
