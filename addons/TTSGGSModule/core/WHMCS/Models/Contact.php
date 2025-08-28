<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

class Contact extends \WHMCS\User\Client\Contact
{
    public function client()
    {
        return $this->belongsTo("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Client", "userid");
    }

    public function orders()
    {
        return $this->hasMany("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Order", "id", "orderid");
    }
}
