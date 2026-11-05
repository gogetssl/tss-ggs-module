<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\repository\whmcs;

use WHMCS\Database\Capsule as DataBase;
use ModulesGarden\Servers\TSSGGSModule\core\Lang;

class Domain {

    /**
     * Checks a domain name (or, with $allowIp, a public IP address). Unicode names (IDN) are accepted in their
     * punycode form - use toAscii() first. The error names the domain that failed.
     */
    public function validateDomain($domain, $wildcard = false, $allowIp = false)
    {
        $domain = trim($domain);
        $name   = $domain;
        if ($wildcard === true) {
            $name = substr($domain, 2);
        }

        if ($allowIp && !$wildcard && filter_var($name, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return true;
        }

        $check = (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $name)
            && preg_match("/^.{1,253}$/", $name)
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $name)
            && preg_match('/[^.\d]/', $name));

        if ($check !== true) {

            if($wildcard === true)
            {
                throw new \Exception(Lang::absoluteT('invalid_wildcard_domain'));
            }

            throw new \Exception(sprintf(Lang::absoluteT('invalid_domain'), $domain));
        }

        return true;
    }

    /**
     * Unicode domain (bücher.example) -> punycode (xn--bcher-kva.example); other names are returned as they are
     */
    public static function toAscii($domain)
    {
        $domain = trim((string)$domain);
        if ($domain === '' || !preg_match('/[^\x20-\x7e]/', $domain) || !function_exists('idn_to_ascii')) {
            return $domain;
        }

        $prefix = strpos($domain, '*.') === 0 ? '*.' : '';
        $ascii  = idn_to_ascii(substr($domain, strlen($prefix)), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

        return $ascii !== false ? $prefix . strtolower($ascii) : $domain;
    }

}
