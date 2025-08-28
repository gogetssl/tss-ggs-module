<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

/**
 * Description of Domain Pricing
 *
 * @var id
 * @var extension
 * @var dnsmanagement
 * @var emailforwarding
 * @var idprotection
 * @var eppcode
 * @var autoreg
 * @var order
 * @var group
 */
class DomainPricing extends \WHMCS\Domains\Extension
{
    public function price()
    {
        return $this->hasMany("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Pricing", "relid");
    }

    public function getExtensionNoDotAttribute()
    {
        return substr($this->extension, 1);
    }

    public function scopeGroupByExtension($query)
    {
        return $query->groupBy('tbldomainpricing.extension');
    }

    public function scopeWithPricing($query)
    {
        return $query->join('tblpricing', function($join) {
            $join->on('tbldomainpricing.id', 'LIKE', 'tblpricing.relid');
        }
        );
    }
}
