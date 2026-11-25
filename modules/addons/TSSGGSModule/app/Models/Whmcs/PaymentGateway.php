<?php

namespace TSSGGSModule\App\Models\Whmcs;

class PaymentGateway extends \WHMCS\Module\GatewaySetting
{
    public function scopeGetNameByGateway($query, $gateway)
    {
        return $query->where('setting', 'name')->where('gateway', $gateway);
    }
}
