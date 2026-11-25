<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Certificates;

use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\ProductRepository;

/**
 * Certificates for public IP addresses whose CSR has no common name (M-19): GoGetSSL "Public IP SAN" (product 144).
 * Every name of the certificate - public IPs or domain names - is entered as a SAN; the first one becomes the main name
 * of the certificate (and of the WHMCS service). Validation by file only (HTTP / HTTPS), no AutoInstall.
 *
 * The product list has no flag for "CSR without common name" yet (asked of the gateway team 2026-11-16): the product
 * is known by its ID, and a future common_name.required = false in /products switches any product over by itself.
 */
class IpCertificate
{
    /** GoGetSSL product IDs whose CSR must have no common name */
    const GGS_PRODUCTS = ['144'];

    /** @var array package id => bool */
    protected static $cache = [];

    /** The WHMCS product is a certificate whose CSR has no common name */
    public static function isProduct($packageId)
    {
        $packageId = (int)$packageId;
        if(array_key_exists($packageId, self::$cache))
        {
            return self::$cache[$packageId];
        }
        $result = false;
        try
        {
            $config = (new ProductRepository())->getProductConfiguration($packageId);
            $remote = (string)($config['product_id'] ?? '');
            $vendor = strtoupper((string)($config['provider'] ?? ''));
            if($vendor === 'GGS' && in_array($remote, self::GGS_PRODUCTS, true))
            {
                $result = true;
            }
            else
            {
                $row = RemoteProduct::where('remoteId', $remote)->where('vendor', (string)($config['provider'] ?? ''))->first();
                $raw = $row ? $row->rawData : [];
                $raw = is_array($raw) ? $raw : (json_decode((string)$raw, true) ?: []);
                $result = isset($raw['common_name']['required']) && $raw['common_name']['required'] === false;
            }
        }
        catch(\Throwable $e)
        {
        }

        return self::$cache[$packageId] = $result;
    }

    /** A public IPv4 / IPv6 address (private and reserved ranges can not be certified) */
    public static function isPublicIp($name)
    {
        return (bool)filter_var((string)$name, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
}
