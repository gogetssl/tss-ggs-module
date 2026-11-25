<?php

namespace TSSGGSModule\App\Models\Whmcs;

class Email extends \WHMCS\Mail\Log
{
    public function client()
    {
        return $this->belongsTo('TSSGGSModule\\App\\Models\\Whmcs\\Client', 'userid');
    }
}
