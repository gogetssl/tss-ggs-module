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

    /**
     * Option names that carry the choice: the module's own, and the legacy TheSSLStore module's "CSR Domain"
     * (Standard / Wildcard), which services migrated from it still have
     */
    const OPTION_NAMES = [self::OPTION . '|%', 'CSR Domain%'];

    /** The client's choice on the service: STANDARD, WILDCARD, or null when the product has no such option */
    public static function serviceChoice(int $serviceId): ?string
    {
        $sub = Capsule::table('tblhostingconfigoptions')
            ->join('tblproductconfigoptions', 'tblproductconfigoptions.id', '=', 'tblhostingconfigoptions.configid')
            ->join('tblproductconfigoptionssub', 'tblproductconfigoptionssub.id', '=', 'tblhostingconfigoptions.optionid')
            ->where('tblhostingconfigoptions.relid', $serviceId)
            ->where(function($q) {
                foreach(self::OPTION_NAMES as $pattern)
                {
                    $q->orWhere('tblproductconfigoptions.optionname', 'like', $pattern);
                }
            })
            ->orderByRaw("CASE WHEN tblproductconfigoptions.optionname LIKE ? THEN 0 ELSE 1 END", [self::OPTION . '|%'])
            ->value('tblproductconfigoptionssub.optionname');
        if($sub === null)
        {
            return null;
        }

        return strpos(strtolower(trim((string)$sub)), self::WILDCARD) === 0 ? self::WILDCARD : self::STANDARD;
    }

    /** The FLEX product (raw catalogue data) of a service, null when the service's product is no FLEX product */
    protected static function flexProductOf(int $serviceId): ?array
    {
        $packageId = (int)Capsule::table('tblhosting')->where('id', $serviceId)->value('packageid');
        $vendor    = (string)Capsule::table('tblproducts')->where('id', $packageId)->value('configoption1');
        $remote    = $packageId ? GatewayOrderService::remoteProduct($packageId) : null;
        $raw       = $remote && is_array($remote->rawData) ? $remote->rawData : null;

        return self::isChoice($raw, $vendor) ? $raw : null;
    }

    /**
     * Refuses a main domain the service may not have. Messages are shown to the client.
     *  - *. only on a wildcard product, or on a FLEX product with the Wildcard choice
     *  - on a FLEX product the main domain matches the choice both ways: the provider charges by the main domain,
     *    so a paid Wildcard ordered as a normal domain would let a later reissue to *. pass at the lower price
     */
    public static function assertCommonName(int $serviceId, string $commonName): void
    {
        $isWildcard = strpos(trim($commonName), '*.') === 0;
        $packageId  = (int)Capsule::table('tblhosting')->where('id', $serviceId)->value('packageid');
        $product    = Capsule::table('tblproducts')->where('id', $packageId)->first(['configoption1', 'configoption2']);
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
        if(self::isChoice($raw, (string)$product->configoption1))
        {
            $choice = self::serviceChoice($serviceId) ?: self::STANDARD;
            if($isWildcard && $choice !== self::WILDCARD)
            {
                throw new \Exception(self::text('needs_wildcard'));
            }
            if(!$isWildcard && $choice === self::WILDCARD)
            {
                throw new \Exception(self::text('needs_wildcard_name'));
            }

            return;
        }
        if($isWildcard && !self::wildcardOnly($raw))
        {
            throw new \Exception(self::text('no_wildcard'));
        }
    }

    /**
     * Upgrade page: the main domain type of an ordered FLEX certificate is fixed (the order was placed and charged
     * as that type - a change would bill or refund the client for nothing the certificate gets); '' when allowed
     */
    public static function changeError(int $serviceId, array $postedOptions): string
    {
        if(!self::flexProductOf($serviceId) || !SanUpgrade::isOrderedCertificate($serviceId))
        {
            return '';
        }
        $current = Capsule::table('tblhostingconfigoptions')
            ->join('tblproductconfigoptions', 'tblproductconfigoptions.id', '=', 'tblhostingconfigoptions.configid')
            ->where('tblhostingconfigoptions.relid', $serviceId)
            ->where(function($q) {
                foreach(self::OPTION_NAMES as $pattern)
                {
                    $q->orWhere('tblproductconfigoptions.optionname', 'like', $pattern);
                }
            })
            ->first(['tblhostingconfigoptions.configid', 'tblhostingconfigoptions.optionid']);
        if($current && isset($postedOptions[$current->configid]) && (int)$postedOptions[$current->configid] !== (int)$current->optionid)
        {
            return self::text('locked');
        }

        return '';
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
        'needs_wildcard'      => 'This certificate was ordered with a standard main domain: use a normal domain (example.com). For a wildcard main domain (*.example.com), order the certificate with the "Wildcard domain" choice.',
        'needs_wildcard_name' => 'This certificate was ordered with a wildcard main domain: enter it as *.example.com.',
        'no_wildcard'         => 'This certificate does not cover a wildcard main domain (*.example.com). Use a normal domain, or order a wildcard certificate.',
        'locked'              => 'The main domain type (standard or wildcard) of an ordered certificate cannot be changed. To change it, order a new certificate.',
    ];
}
