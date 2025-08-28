<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

class Transaction extends \WHMCS\Billing\Payment\Transaction
{
    public function client()
    {
        return $this->belongsTo("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Client", "userid");
    }
    public function invoice()
    {
        return $this->belongsTo("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Invoice", "invoiceid");
    }
}
