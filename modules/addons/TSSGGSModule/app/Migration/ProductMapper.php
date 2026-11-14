<?php

namespace ModulesGarden\TSSGGSModule\App\Migration;

use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;

/**
 * Finds the gateway product (the RemoteProducts table, synced in wizard step 4) for a legacy product reference:
 *  - GGS: the GoGetSSL API product id, which the gateway keeps as its own id
 *  - TSS: the TheSSLStore product code, matched against the code fields the gateway exposes in the product's raw data
 */
class ProductMapper
{
    /** Keys of RemoteProducts.rawData that may hold the TSS product code (checked in this order) */
    const TSS_CODE_KEYS = ['tss_code', 'product_code', 'code', 'legacy_code', 'sku'];

    protected $byVendor = [];

    /** The administrator's choice for legacy products without a match: legacy reference => gateway product id | RECORD */
    protected $overrides = [];

    const RECORD = 'record';

    public function setOverrides(array $map): void
    {
        $this->overrides = [];
        foreach($map as $ref => $target)
        {
            $ref = strtolower(trim((string)$ref));
            if($ref !== '' && (string)$target !== '')
            {
                $this->overrides[$ref] = (string)$target;
            }
        }
    }

    /** The choice made for a legacy reference, null when none */
    public function override(string $ref): ?string
    {
        return $this->overrides[strtolower(trim($ref))] ?? null;
    }

    public function isRecord(string $ref): bool
    {
        return $this->override($ref) === self::RECORD;
    }

    /** Gateway products of a vendor, for the administrator's choice (sorted by brand and name) */
    public function vendorProducts(string $vendor): array
    {
        $list = $this->byVendor[strtoupper($vendor)] ?? [];
        usort($list, function($a, $b) { return strcasecmp((string)$a->brand . ' ' . $a->name, (string)$b->brand . ' ' . $b->name); });

        return $list;
    }

    /** The gateway product chosen for a legacy reference (null: none chosen, or kept as a record) */
    protected function chosen(string $vendor, string $ref)
    {
        $target = $this->override($ref);
        if($target === null || $target === self::RECORD)
        {
            return null;
        }
        foreach($this->byVendor[$vendor] ?? [] as $product)
        {
            if((string)$product->remoteId === $target)
            {
                return $product;
            }
        }

        return null;
    }

    public function __construct()
    {
        foreach(RemoteProduct::all() as $product)
        {
            $vendor = strtoupper((string)$product->vendor);
            $this->byVendor[$vendor][] = $product;
        }
    }

    public function hasVendor(string $vendor): bool
    {
        return !empty($this->byVendor[strtoupper($vendor)]);
    }

    /**
     * @return RemoteProduct|null
     */
    public function forGgs($apiProductId)
    {
        if($this->override((string)$apiProductId) !== null)
        {
            return $this->chosen('GGS', (string)$apiProductId);
        }
        $apiProductId = (int)$apiProductId;
        foreach($this->byVendor['GGS'] ?? [] as $product)
        {
            if((int)$product->remoteId === $apiProductId)
            {
                return $product;
            }
        }

        return null;
    }

    /**
     * @return RemoteProduct|null
     */
    public function forTss(string $productCode)
    {
        $productCode = strtolower(trim($productCode));
        if($productCode === '')
        {
            return null;
        }
        if($this->override($productCode) !== null)
        {
            return $this->chosen('TSS', $productCode);
        }

        foreach($this->byVendor['TSS'] ?? [] as $product)
        {
            $raw = is_array($product->rawData) ? $product->rawData : [];
            foreach(self::TSS_CODE_KEYS as $key)
            {
                if(isset($raw[$key]) && strtolower(trim((string)$raw[$key])) === $productCode)
                {
                    return $product;
                }
            }
            // some gateways expose the code as the id itself
            if(strtolower(trim((string)$product->remoteId)) === $productCode)
            {
                return $product;
            }
        }

        return null;
    }

    /**
     * Unified product configoption values for a mapped remote product
     */
    public static function configOptionsFor(RemoteProduct $product, int $includedSan, int $includedSanWildcard, bool $priceAuto): array
    {
        $raw = is_array($product->rawData) ? $product->rawData : [];
        $dcv = isset($raw['dcv']) ? (is_array($raw['dcv']) ? implode(',', $raw['dcv']) : (string)$raw['dcv']) : '';

        return [
            'configoption1' => strtoupper((string)$product->vendor),
            'configoption2' => (string)$product->remoteId,
            'configoption3' => (string)$product->brand,
            'configoption4' => (string)$product->validation,
            'configoption5' => $dcv,
            'configoption6' => $priceAuto ? 'on' : '',
            'configoption7' => (string)$includedSan,
            'configoption8' => (string)$includedSanWildcard,
            'configoption9' => (string)($product->category ?: 'tls'),
        ];
    }
}
