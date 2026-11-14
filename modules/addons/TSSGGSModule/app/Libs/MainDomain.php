<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use WHMCS\Database\Capsule;

/**
 * The main domain (common name) of a certificate: a normal domain or a wildcard (*.example.com).
 *
 * - Wildcard products take only a wildcard; normal products only a normal domain.
 * - "FLEX" products (DigiCert family at TheSSLStore) take either, at different prices: the provider charges its
 *   wildcard base price when the main domain is a wildcard. The client chooses on the order with the configurable
 *   option "main_domain" (Standard / Wildcard), the wildcard choice priced at the difference.
 * A *. main domain is accepted only on a wildcard product, or on a FLEX product where Wildcard was paid for.
 *
 * VENDORS: the providers whose FLEX products get the option (GoGetSSL to follow - same data, same logic).
 */
class MainDomain
{
    const OPTION   = 'main_domain';
    const STANDARD = 'standard';
    const WILDCARD = 'wildcard';
    const VENDORS  = ['TSS'];

    /** item name of the wildcard surcharge in PricingService */
    const PRICE_ITEM = 'main_wildcard';

    const LABELS = [
        'option'       => 'Main domain',
        self::STANDARD => 'Standard domain (example.com)',
        self::WILDCARD => 'Wildcard domain (*.example.com)',
    ];

    // ---- what the provider allows ---- //

    /** The product takes both a normal and a wildcard main domain at two prices (FLEX) */
    public static function isChoice(?array $raw, string $vendor): bool
    {
        if(!$raw || !in_array(strtoupper($vendor), self::VENDORS, true))
        {
            return false;
        }
        if(empty($raw['common_name']['single_allowed']) || empty($raw['common_name']['wildcard_allowed']))
        {
            return false;
        }
        foreach((array)($raw['prices'] ?? []) as $price)
        {
            if(isset($price['base']['single']['selling'], $price['base']['wildcard']['selling']))
            {
                return true;
            }
        }

        return false;
    }

    /** The product takes only a wildcard main domain */
    public static function wildcardOnly(?array $raw): bool
    {
        return $raw && !empty($raw['common_name']['wildcard_allowed']) && empty($raw['common_name']['single_allowed']);
    }

    /** Extra provider cost of a wildcard main domain for a term (null when the product has no such choice) */
    public static function wildcardCost(?array $raw, int $term): ?float
    {
        foreach((array)($raw['prices'] ?? []) as $price)
        {
            if((int)($price['term'] ?? 0) === $term && isset($price['base']['single']['selling'], $price['base']['wildcard']['selling']))
            {
                return max(0.0, (float)$price['base']['wildcard']['selling'] - (float)$price['base']['single']['selling']);
            }
        }

        return null;
    }

    // ---- the service ---- //

    /** The client's choice on the service: STANDARD, WILDCARD, or null when the product has no such option */
    public static function serviceChoice(int $serviceId): ?string
    {
        $sub = Capsule::table('tblhostingconfigoptions')
            ->join('tblproductconfigoptions', 'tblproductconfigoptions.id', '=', 'tblhostingconfigoptions.configid')
            ->join('tblproductconfigoptionssub', 'tblproductconfigoptionssub.id', '=', 'tblhostingconfigoptions.optionid')
            ->where('tblhostingconfigoptions.relid', $serviceId)
            ->where('tblproductconfigoptions.optionname', 'like', self::OPTION . '|%')
            ->value('tblproductconfigoptionssub.optionname');
        if($sub === null)
        {
            return null;
        }

        return strpos(strtolower((string)$sub), self::WILDCARD) === 0 ? self::WILDCARD : self::STANDARD;
    }

    /**
     * Refuses a main domain the service may not have: *. on a product without wildcard, or on a FLEX product
     * where Wildcard was not paid for. Messages are shown to the client.
     */
    public static function assertCommonName(int $serviceId, string $commonName): void
    {
        if(strpos(trim($commonName), '*.') !== 0)
        {
            return;
        }
        $packageId = (int)Capsule::table('tblhosting')->where('id', $serviceId)->value('packageid');
        $product   = Capsule::table('tblproducts')->where('id', $packageId)->first(['configoption1', 'configoption2']);
        if(!$product)
        {
            return;
        }
        $remote = GatewayOrderService::remoteProduct($packageId);
        $raw    = $remote && is_array($remote->rawData) ? $remote->rawData : null;
        if(!$raw)
        {
            return; // no catalogue entry to judge by: the provider decides
        }
        if(self::wildcardOnly($raw))
        {
            return;
        }
        if(self::isChoice($raw, (string)$product->configoption1))
        {
            if(self::serviceChoice($serviceId) === self::WILDCARD)
            {
                return;
            }
            throw new \Exception(self::text('needs_wildcard'));
        }
        throw new \Exception(self::text('no_wildcard'));
    }

    // ---- the product option ---- //

    /**
     * Creates or repairs the "main_domain" option of a FLEX product: a dropdown with Standard (no charge) and
     * Wildcard (the difference). Prices are set only where none exist yet: at the reseller cost plus $margin
     * (null: the cost, as the other repaired options). Returns what was done.
     */
    public static function ensureOption(int $productId, array $raw, ?float $margin = null, float $rate = 1.0): array
    {
        $product = Capsule::table('tblproducts')->where('id', $productId)->first();
        if(!$product || !self::isChoice($raw, (string)$product->configoption1))
        {
            return [];
        }
        $done   = [];
        $option = Capsule::table('tblproductconfiglinks')
            ->join('tblproductconfigoptions', 'tblproductconfigoptions.gid', '=', 'tblproductconfiglinks.gid')
            ->where('tblproductconfiglinks.pid', $productId)->where('tblproductconfigoptions.optionname', 'like', self::OPTION . '|%')
            ->first(['tblproductconfigoptions.id', 'tblproductconfigoptions.hidden']);
        if(!$option)
        {
            $optionId = (int)Capsule::table('tblproductconfigoptions')->insertGetId([
                'gid' => ProductCatalog::configGroupOf($product), 'optionname' => self::OPTION . '|' . self::LABELS['option'],
                'optiontype' => 1, 'qtyminimum' => 0, 'qtymaximum' => 0, 'order' => -1, 'hidden' => 0,
            ]);
            $done[] = self::OPTION . ' created';
        }
        else
        {
            $optionId = (int)$option->id;
            Capsule::table('tblproductconfigoptions')->where('id', $optionId)->update(['optiontype' => 1, 'hidden' => 0]);
        }

        foreach([self::STANDARD => 0, self::WILDCARD => 1] as $choice => $sort)
        {
            $subId = (int)Capsule::table('tblproductconfigoptionssub')->where('configid', $optionId)->where('optionname', 'like', $choice . '|%')->value('id');
            if(!$subId)
            {
                $subId  = (int)Capsule::table('tblproductconfigoptionssub')->insertGetId([
                    'configid' => $optionId, 'optionname' => $choice . '|' . self::LABELS[$choice], 'sortorder' => $sort, 'hidden' => 0,
                ]);
                $done[] = self::OPTION . ' ' . $choice . ' added';
            }
            self::ensurePricing($product, $subId, $choice === self::WILDCARD ? $raw : null, $margin, $rate);
        }

        return $done;
    }

    /** Pricing rows of a sub-option in every currency, only where missing (Standard: free) */
    protected static function ensurePricing($product, int $subId, ?array $raw, ?float $margin, float $rate): void
    {
        $pricing = new PricingService();
        foreach(PricingService::currencies() as $currency)
        {
            if(Capsule::table('tblpricing')->where('type', 'configoptions')->where('relid', $subId)->where('currency', $currency->id)->exists())
            {
                continue;
            }
            $row = ['type' => 'configoptions', 'relid' => $subId, 'currency' => $currency->id, 'msetupfee' => 0, 'qsetupfee' => 0, 'ssetupfee' => 0,
                    'asetupfee' => 0, 'bsetupfee' => 0, 'tsetupfee' => 0, 'monthly' => 0, 'quarterly' => 0, 'semiannually' => 0, 'annually' => 0, 'biennially' => 0, 'triennially' => 0];
            if($raw)
            {
                foreach(PricingService::TERMS as $term => $column)
                {
                    $cost = self::wildcardCost($raw, $term);
                    if($cost !== null)
                    {
                        // the import's margin (sell() converts the base-currency cost itself), else the reseller cost
                        $row[$column] = $margin === null ? $pricing->convert($cost, $currency->id)
                            : FinancePolicy::sell($cost * $rate, $margin, $term, $currency->id, false);
                    }
                }
            }
            Capsule::table('tblpricing')->insert($row);
        }
    }

    /** Client / admin texts (server module language, English when it is not loaded) */
    public static function text(string $key): string
    {
        $text = '';
        try
        {
            if(class_exists('\ModulesGarden\Servers\TSSGGSModule\core\Lang'))
            {
                $text = html_entity_decode((string)\ModulesGarden\Servers\TSSGGSModule\core\Lang::absoluteT('mainDomain', $key), ENT_QUOTES, 'UTF-8');
            }
        }
        catch(\Throwable $e)
        {
            $text = '';
        }
        if($text === '' || $text === $key || strpos($text, 'mainDomain') !== false || strpos($text, '_LANG[') !== false)
        {
            $text = self::ENGLISH[$key] ?? $key;
        }

        return $text;
    }

    const ENGLISH = [
        'needs_wildcard' => 'A wildcard main domain (*.example.com) needs the "Wildcard domain" choice of this certificate. Upgrade the "Main domain" option of the service first, or use a normal domain.',
        'no_wildcard'    => 'This certificate does not cover a wildcard main domain (*.example.com). Use a normal domain, or order a wildcard certificate.',
    ];
}
