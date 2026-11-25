<?php

namespace TSSGGSModule\App\Models\Whmcs;

class InvoiceItem extends \WHMCS\Billing\Invoice\Item
{
    public function client()
    {
        return $this->hasOne('TSSGGSModule\\App\\Models\\Whmcs\\Client', 'id', 'userid');
    }

    public function domain()
    {
        return $this->hasOne('TSSGGSModule\\App\\Models\\Whmcs\\Domain', 'id', 'relid');
    }

    public function service()
    {
        return $this->hasOne('TSSGGSModule\\App\\Models\\Whmcs\\Service', 'id', 'relid');
    }

    public function serviceAddon()
    {
        return $this->hasOne('TSSGGSModule\\App\\Models\\Whmcs\\ServiceAddon', 'id', 'relid');
    }

    /**
     * @deprecated - use service
     */
    public function hosting()
    {
        return $this->hasOne('TSSGGSModule\\App\\Models\\Whmcs\\Hosting', 'id', 'relid');
    }

    /**
     * @deprecated - use serviceAddon
     */
    public function hostingAddon()
    {
        return $this->hasOne('TSSGGSModule\\App\\Models\\Whmcs\\HostingAddon', 'id', 'relid');
    }

    public function invoice()
    {
        return $this->belongsTo('TSSGGSModule\\App\\Models\\Whmcs\\Invoice', 'invoiceid');
    }

    public function upgrade()
    {
        return $this->hasOne('TSSGGSModule\\App\\Models\\Whmcs\\Upgrade', 'id', 'relid');
    }
}
