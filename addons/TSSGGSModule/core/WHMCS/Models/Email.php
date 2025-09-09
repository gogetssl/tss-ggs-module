<?php

namespace ModulesGarden\TSSGGSModule\Core\WHMCS\Models;

class Email extends \WHMCS\Mail\Log
{
    public function client()
    {
        return $this->belongsTo("ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Client", 'userid');
    }
}
