<?php

namespace ModulesGarden\TSSGGSModule\App\Models\Whmcs;

class Transaction extends \WHMCS\Billing\Payment\Transaction
{
    public function client()
    {
        return $this->belongsTo('ModulesGarden\\TSSGGSModule\\App\\Models\\Whmcs\\Client', "userid");
    }
    public function invoice()
    {
        return $this->belongsTo('ModulesGarden\\TSSGGSModule\\App\\Models\\Whmcs\\Invoice', "invoiceid");
    }
}
