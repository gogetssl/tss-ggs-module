<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use WHMCS\Database\Capsule;

/**
 * The free second name of a certificate ("twin" of the common name): www.<cn> for <cn>, <cn> for www.<cn>, and the
 * base domain for *.<cn>.
 *
 * TheSSLStore adds it by itself to every DigiCert, Thawte, GeoTrust, RapidSSL and Sectigo TLS order (gateway answer
 * to GW-030, 2026-11-17). Sent in alternative_names it becomes a paid SAN in TheSSLStore's DNSNames, and a product
 * without SAN slots then refuses the order ("DNSNames count is invalid"). So the module never sends it to the
 * TheSSLStore gateway: not on a new order, a renewal or a reissue, and it does not count against the paid SANs.
 *
 * GoGetSSL adds the free name only to orders without SANs, so a GoGetSSL request is left as it is.
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

    /** true when the vendor adds the free name itself (TheSSLStore) */
    public static function addedByVendor($vendor)
    {
        return strtoupper(trim((string)$vendor)) === 'TSS';
    }

    /** true when the service's product is sold through TheSSLStore */
    public static function addedForService($serviceId)
    {
        try
        {
            $packageId = (int)Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('packageid');
            if(!$packageId)
            {
                return false;
            }
            $product = (new \ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\ProductRepository())->getProductConfiguration($packageId);

            return self::addedByVendor($product['provider'] ?? '');
        }
        catch(\Throwable $e)
        {
            return false;
        }
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
     * A TLS request (new order, renewal, reissue) for the vendor without the free name in any SAN list. An emptied
     * alternative_names is left out, as the module never sends an empty one on a new order.
     */
    public static function stripRequest($vendor, array $data)
    {
        $commonName = $data['common_name']['name'] ?? '';
        if(!self::addedByVendor($vendor) || self::twin($commonName) === '')
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
