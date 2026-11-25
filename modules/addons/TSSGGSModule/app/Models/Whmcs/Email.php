<?php

namespace ModulesGarden\TSSGGSModule\App\Models\Whmcs;

class Email extends \WHMCS\Mail\Log
{
    public function client()
    {
        return $this->belongsTo('ModulesGarden\\TSSGGSModule\\App\\Models\\Whmcs\\Client', 'userid');
    }
}
