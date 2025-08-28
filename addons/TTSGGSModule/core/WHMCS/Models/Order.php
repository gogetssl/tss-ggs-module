<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

class Order extends \WHMCS\Order\Order
{
    public function addons()
    {
        return $this->hasMany('ModulesGarden\TTSGGSModule\Core\WHMCS\Models\HostingAddon', 'orderid');
    }

    public function client()
    {
        return $this->belongsTo('ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Client', 'userid');
    }

    public function domains()
    {
        return $this->hasMany('ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Domain', 'orderid');
    }

    public function services()
    {
        return $this->hasMany('ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Hosting', 'orderid');
    }

    /**
     * @deprecated - use services
     */
    public function hostings()
    {
        return $this->hasMany('ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Hosting', 'orderid');
    }

    public function invoice()
    {
        return $this->hasOne('ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Invoice', 'id', 'invoiceid');
    }

    public function upgrades(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Upgrade', 'orderid');
    }

    public function promotion()
    {
        return $this->hasOne("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Promotion", "code", "promocode");
    }
}
