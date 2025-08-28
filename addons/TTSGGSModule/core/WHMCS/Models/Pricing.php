<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

class Pricing extends \WHMCS\Billing\Pricing
{
    const TYPE_PRODUCT         = "product";
    const TYPE_ADDON           = "addon";
    const TYPE_CONFIGOPTION    = "configoptions";
    const TYPE_DOMAIN_REGISTER = "domainregister";
    const TYPE_DOMAIN_TRANSFER = "domaintransfer";
    const TYPE_DOMAIN_RENEW    = "domainrenew";
    const TYPE_DOMAIN_ADDON    = "domainaddons";
    const TYPE_USAGE           = "usage";

    public function currencyModel()
    {
        return $this->belongsTo("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Currency", 'currency');
    }

    /**
     * Adds query condition to limit result records only to domains pricing
     */
    public function domainPricing()
    {
        $this->whereIn('tblpricing.type', [self::TYPE_DOMAIN_TRANSFER, self::TYPE_DOMAIN_RENEW, self::TYPE_DOMAIN_REGISTER]);

        return $this;
    }
}
