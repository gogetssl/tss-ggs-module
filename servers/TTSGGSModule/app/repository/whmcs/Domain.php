<?php

namespace ModulesGarden\Servers\TTSGGSModule\app\repository\whmcs;

use WHMCS\Database\Capsule as DataBase;
use ModulesGarden\Servers\TTSGGSModule\core\Lang;

class Domain {

    public function validateDomain($domain, $wildcard = false)
    {
        $domain = trim($domain);
        if ($wildcard === true) {
            $domain = substr($domain, 2);
        }

        $check = (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain)
            && preg_match("/^.{1,253}$/", $domain)
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain)
            && preg_match('/[^.\d]/', $domain));

        if ($check !== true) {

            if($wildcard === true)
            {
                throw new \Exception(Lang::absoluteT('invalid_wildcard_domain'));
            }

            throw new \Exception(Lang::absoluteT('invalid_domain'));
        }

        return true;
    }

}