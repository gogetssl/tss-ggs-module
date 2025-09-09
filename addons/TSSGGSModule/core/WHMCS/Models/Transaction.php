<?php

namespace ModulesGarden\TSSGGSModule\Core\WHMCS\Models;

class Transaction extends \WHMCS\Billing\Payment\Transaction
{
    public function client()
    {
        return $this->belongsTo("ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Client", "userid");
    }
    public function invoice()
    {
        return $this->belongsTo("ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Invoice", "invoiceid");
    }
}
