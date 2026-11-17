<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use WHMCS\Database\Capsule;

/**
 * The free second name of a certificate ("twin" of the common name): www.<cn> for <cn>, <cn> for www.<cn>, and the
 * base domain for *.<cn>.
 *
 * Who adds it (tested 2026-11-17 on the TheSSLStore sandbox and the GoGetSSL test account):
 *  - a product WITHOUT SAN support (gateway catalogue: san = null, e.g. RapidSSL, PositiveSSL): the vendor adds the
 *    twin itself. Sent in alternative_names, TheSSLStore takes it as a paid SAN and refuses the order of such a
 *    product ("DNSNames count is invalid", GW-030), so it is never sent to TheSSLStore for these products.
 *  - a product WITH SAN support (multi-domain, UCC, DigiCert Flex): nobody adds it. A www name the client chose is
 *    an ordinary additional domain and is sent as such (both vendors).
 * GoGetSSL requests are never changed: GoGetSSL takes the twin of a single-name product without an error.
 */
class FreeName
{
    /** the request keys that carry SAN lists */
    const SAN_KEYS = ['alternative_names', 'existing_alternative_names', 'submitted_alternative_names'];

    /** the free second name of a common name, '' for none (IP address, empty) */
    public static function twin($commonName)
    {
        $commonName = strtolower(trim((string)$commonName));
        if($commonName === '' || filter_var($commonName, FILTER_VALIDATE_IP))
        {
            return '';
        }
        if(strpos($commonName, '*.') === 0)
        {
            return substr($commonName, 2);
        }
        if(strpos($commonName, 'www.') === 0)
        {
            return substr($commonName, 4);
        }

        return 'www.' . $commonName;
    }

    /**
     * true when the gateway product has no SAN support (catalogue san missing, null or max 0): the vendor then adds the
     * free name itself. Unknown products count as having SAN support (nothing is removed).
     */
    public static function productWithoutSans($vendor, $productId)
    {
        try
        {
            $remote = RemoteProduct::where('remoteId', (string)$productId)->where('vendor', strtoupper((string)$vendor))->first();
            if(!$remote)
            {
                return false;
            }
            $raw = $remote->rawData;
            $raw = is_array($raw) ? $raw : (json_decode((string)$raw, true) ?: []);
            // the catalogue leaves san out (or null) for a product without SAN support
            $san = $raw['san'] ?? null;

            return $san === null || (is_array($san) && (int)($san['max'] ?? 0) === 0 && (int)($san['included']['single'] ?? 0) === 0);
        }
        catch(\Throwable $e)
        {
            return false;
        }
    }

    /** true when the free name must not be sent: TheSSLStore and a product without SAN support */
    public static function addedByVendor($vendor, $productId)
    {
        return strtoupper(trim((string)$vendor)) === 'TSS' && self::productWithoutSans($vendor, $productId);
    }

    /** [vendor, gateway product id] of a service, ['', ''] when unknown */
    public static function productOfService($serviceId)
    {
        try
        {
            $packageId = (int)Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('packageid');
            if(!$packageId)
            {
                return ['', ''];
            }
            $product = (new \ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\ProductRepository())->getProductConfiguration($packageId);

            return [(string)($product['provider'] ?? ''), (string)($product['product_id'] ?? '')];
        }
        catch(\Throwable $e)
        {
            return ['', ''];
        }
    }

    /** addedByVendor() for the service's product */
    public static function addedForService($serviceId)
    {
        list($vendor, $productId) = self::productOfService($serviceId);

        return $vendor !== '' && self::addedByVendor($vendor, $productId);
    }

    /** true when $name is the free name of $commonName */
    public static function isTwin($name, $commonName)
    {
        $twin = self::twin($commonName);

        return $twin !== '' && strtolower(trim((string)$name)) === $twin;
    }

    /**
     * A SAN list without the free name of $commonName. Entries are names or ['name' => .., 'validation_method' => ..];
     * the order of the others is kept.
     */
    public static function strip(array $names, $commonName)
    {
        $kept = [];
        foreach($names as $entry)
        {
            $name = is_array($entry) ? ($entry['name'] ?? '') : $entry;
            if(!self::isTwin($name, $commonName))
            {
                $kept[] = $entry;
            }
        }

        return $kept;
    }

    /**
     * A TLS request (new order, renewal: product {id} in the request; reissue: $productId) without the free name in
     * any SAN list, when the vendor adds it itself (addedByVendor). An emptied alternative_names is left out, as the
     * module never sends an empty one on a new order.
     */
    public static function stripRequest($vendor, array $data, $productId = null)
    {
        $commonName = $data['common_name']['name'] ?? '';
        $productId  = $productId !== null ? $productId : ($data['product']['id'] ?? null);
        if($productId === null || self::twin($commonName) === '' || !self::addedByVendor($vendor, $productId))
        {
            return $data;
        }
        foreach(self::SAN_KEYS as $key)
        {
            if(isset($data[$key]) && is_array($data[$key]))
            {
                $data[$key] = self::strip($data[$key], $commonName);
            }
        }
        if(isset($data['alternative_names']) && $data['alternative_names'] === [])
        {
            unset($data['alternative_names']);
        }

        return $data;
    }
}
