<?php

namespace TSSGGSModule\App\Models\Whmcs;

class Invoice extends \WHMCS\Billing\Invoice
{
    public function client()
    {
        return $this->belongsTo('TSSGGSModule\\App\\Models\\Whmcs\\Client', 'userid');
    }

    public function items()
    {
        return $this->hasMany('TSSGGSModule\\App\\Models\\Whmcs\\InvoiceItem', 'invoiceid');
    }

    public function order()
    {
        return $this->belongsTo('TSSGGSModule\\App\\Models\\Whmcs\\Order', 'id', 'invoiceid');
    }

    public function transactions()
    {
        return $this->hasMany('TSSGGSModule\\App\\Models\\Whmcs\\Transaction', 'invoiceid');
    }

    public function getAmountpaidAttribute()
    {
        $total = 0;
        foreach ($this->transactions as $trans)
        {
            $total += $trans->amountin - $trans->amountout;
        }

        return $total;
    }
}
