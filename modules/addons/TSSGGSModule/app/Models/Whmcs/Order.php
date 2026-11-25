<?php

namespace TSSGGSModule\App\Models\Whmcs;

class Order extends \WHMCS\Order\Order
{
    public function addons()
    {
        return $this->hasMany('TSSGGSModule\App\Models\Whmcs\HostingAddon', 'orderid');
    }

    public function client()
    {
        return $this->belongsTo('TSSGGSModule\App\Models\Whmcs\Client', 'userid');
    }

    public function domains()
    {
        return $this->hasMany('TSSGGSModule\App\Models\Whmcs\Domain', 'orderid');
    }

    public function services()
    {
        return $this->hasMany('TSSGGSModule\App\Models\Whmcs\Hosting', 'orderid');
    }

    /**
     * @deprecated - use services
     */
    public function hostings()
    {
        return $this->hasMany('TSSGGSModule\App\Models\Whmcs\Hosting', 'orderid');
    }

    public function invoice()
    {
        return $this->hasOne('TSSGGSModule\App\Models\Whmcs\Invoice', 'id', 'invoiceid');
    }

    public function upgrades(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('TSSGGSModule\App\Models\Whmcs\Upgrade', 'orderid');
    }

    public function promotion()
    {
        return $this->hasOne('TSSGGSModule\\App\\Models\\Whmcs\\Promotion', "code", "promocode");
    }
}
