<?php

namespace TSSGGSModule\App\Models\Whmcs;

class Service extends \WHMCS\Service\Service
{
    public function addons()
    {
        return $this->hasMany('TSSGGSModule\\App\\Models\\Whmcs\\ServiceAddon', 'hostingid');
    }

    public function cancellationRequests()
    {
        return $this->hasOne('TSSGGSModule\\App\\Models\\Whmcs\\CancelRequest', 'relid');
    }

    public function client()
    {
        return $this->belongsTo('TSSGGSModule\\App\\Models\\Whmcs\\Client', 'userid');
    }

    public function configOptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('TSSGGSModule\\App\\Models\\Whmcs\\ServiceConfigOption', 'relid');
    }

    public function customFieldValues()
    {
        return $this->hasMany('TSSGGSModule\\App\\Models\\Whmcs\\CustomFieldValue', "relid");
    }

    public function order()
    {
        return $this->belongsTo('TSSGGSModule\\App\\Models\\Whmcs\\Order', 'orderid');
    }

    public function product()
    {
        return $this->belongsTo('TSSGGSModule\\App\\Models\\Whmcs\\Product', 'packageid');
    }

    public function server()
    {
        return $this->belongsTo('TSSGGSModule\\App\\Models\\Whmcs\\Server', 'server');
    }

    public function promotion()
    {
        return $this->hasMany('TSSGGSModule\\App\\Models\\Whmcs\\Promotion', "id", "promoid");
    }

    public function paymentGateway()
    {
        return $this->hasMany('TSSGGSModule\\App\\Models\\Whmcs\\PaymentGateway', "gateway", "paymentmethod");
    }
}
