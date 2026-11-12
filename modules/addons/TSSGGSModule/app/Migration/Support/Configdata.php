<?php

namespace ModulesGarden\TSSGGSModule\App\Migration\Support;

/**
 * tblsslorders.configdata is JSON on WHMCS 7.3+, PHP-serialized on older installs and in very old module versions.
 */
class Configdata
{
    public static function decode($raw): array
    {
        if(is_array($raw))
        {
            return $raw;
        }

        $raw = (string)$raw;
        if(trim($raw) === '')
        {
            return [];
        }

        $data = json_decode($raw, true);
        if(is_array($data))
        {
            return $data;
        }

        // Legacy PHP-serialized data; objects are not expected, so disallow them
        $data = @unserialize($raw, ['allowed_classes' => false]);

        return is_array($data) ? $data : [];
    }

    /**
     * Value of $key; falls back through $alternatives; $default when none is set
     */
    public static function get(array $data, string $key, $default = null, array $alternatives = [])
    {
        foreach(array_merge([$key], $alternatives) as $candidate)
        {
            if(isset($data[$candidate]) && $data[$candidate] !== '' && $data[$candidate] !== null)
            {
                return $data[$candidate];
            }
        }

        return $default;
    }

    /**
     * PEM block or empty string
     */
    public static function pem($value): string
    {
        $value = trim((string)$value);

        return strpos($value, '-----BEGIN') === 0 ? $value : '';
    }

    /**
     * Private key stored by the legacy modules: WHMCS encrypt() blob or a plain PEM (very old rows).
     * Returns the PEM or '' when it cannot be read (different cc_encryption_hash, empty value).
     */
    public static function privateKeyPem($value): string
    {
        $value = trim((string)$value);
        if($value === '')
        {
            return '';
        }
        if(strpos($value, '-----BEGIN') === 0)
        {
            return $value;
        }

        try
        {
            $decrypted = trim((string)\decrypt($value));
        }
        catch(\Throwable $e)
        {
            return '';
        }

        return strpos($decrypted, '-----BEGIN') === 0 ? $decrypted : '';
    }

    /**
     * TSS stores "*" of wildcard names as "star" in SAN lists and approver maps
     */
    public static function unstar(string $domain): string
    {
        return preg_replace('/^star\./i', '*.', trim($domain));
    }

    /**
     * Splits a legacy SAN list (newline / comma / space separated) into lowercase domains
     */
    public static function domainList($raw): array
    {
        if(is_array($raw))
        {
            $raw = implode("\n", $raw);
        }
        $items = preg_split('/[\s,;]+/', strtolower(trim((string)$raw)));
        $items = array_map([self::class, 'unstar'], array_filter(array_map('trim', (array)$items)));

        return array_values(array_unique($items));
    }
}
