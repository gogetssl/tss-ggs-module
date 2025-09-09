<?php

namespace ModulesGarden\TSSGGSModule\Core\WHMCS\Models;

class Contact extends \WHMCS\User\Client\Contact
{
    public function client()
    {
        return $this->belongsTo("ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Client", "userid");
    }

    public function orders()
    {
        return $this->hasMany("ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Order", "id", "orderid");
    }
}
