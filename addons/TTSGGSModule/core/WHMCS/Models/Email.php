<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

class Email extends \WHMCS\Mail\Log
{
    public function client()
    {
        return $this->belongsTo("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Client", 'userid');
    }
}
