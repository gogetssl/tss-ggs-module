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
