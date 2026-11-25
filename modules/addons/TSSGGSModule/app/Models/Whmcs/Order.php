<?php

namespace ModulesGarden\TSSGGSModule\App\Models\Whmcs;

class Order extends \WHMCS\Order\Order
{
    public function addons()
    {
        return $this->hasMany('ModulesGarden\TSSGGSModule\App\Models\Whmcs\HostingAddon', 'orderid');
    }

    public function client()
    {
        return $this->belongsTo('ModulesGarden\TSSGGSModule\App\Models\Whmcs\Client', 'userid');
    }

    public function domains()
    {
        return $this->hasMany('ModulesGarden\TSSGGSModule\App\Models\Whmcs\Domain', 'orderid');
    }

    public function services()
    {
        return $this->hasMany('ModulesGarden\TSSGGSModule\App\Models\Whmcs\Hosting', 'orderid');
    }

    /**
     * @deprecated - use services
     */
    public function hostings()
    {
        return $this->hasMany('ModulesGarden\TSSGGSModule\App\Models\Whmcs\Hosting', 'orderid');
    }

    public function invoice()
    {
        return $this->hasOne('ModulesGarden\TSSGGSModule\App\Models\Whmcs\Invoice', 'id', 'invoiceid');
    }

    public function upgrades(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('ModulesGarden\TSSGGSModule\App\Models\Whmcs\Upgrade', 'orderid');
    }

    public function promotion()
    {
        return $this->hasOne('ModulesGarden\\TSSGGSModule\\App\\Models\\Whmcs\\Promotion', "code", "promocode");
    }
}
