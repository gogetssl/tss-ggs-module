<?php

namespace TSSGGSModule\App\Models\Whmcs;

class Contact extends \WHMCS\User\Client\Contact
{
    public function client()
    {
        return $this->belongsTo('TSSGGSModule\\App\\Models\\Whmcs\\Client', "userid");
    }

    public function orders()
    {
        return $this->hasMany('TSSGGSModule\\App\\Models\\Whmcs\\Order', "id", "orderid");
    }
}
