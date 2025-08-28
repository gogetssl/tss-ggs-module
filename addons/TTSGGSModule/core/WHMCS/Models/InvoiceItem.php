<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

class InvoiceItem extends \WHMCS\Billing\Invoice\Item
{
    public function client()
    {
        return $this->hasOne("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Client", 'id', 'userid');
    }

    public function domain()
    {
        return $this->hasOne("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Domain", 'id', 'relid');
    }

    public function service()
    {
        return $this->hasOne("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Service", 'id', 'relid');
    }

    public function serviceAddon()
    {
        return $this->hasOne("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\ServiceAddon", 'id', 'relid');
    }

    /**
     * @deprecated - use service
     */
    public function hosting()
    {
        return $this->hasOne("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Hosting", 'id', 'relid');
    }

    /**
     * @deprecated - use serviceAddon
     */
    public function hostingAddon()
    {
        return $this->hasOne("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\HostingAddon", 'id', 'relid');
    }

    public function invoice()
    {
        return $this->belongsTo("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Invoice", 'invoiceid');
    }

    public function upgrade()
    {
        return $this->hasOne("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Upgrade", 'id', 'relid');
    }
}
