<?php

namespace ModulesGarden\TSSGGSModule\Core\WHMCS\Models;

class PaymentGateway extends \WHMCS\Module\GatewaySetting
{
    public function scopeGetNameByGateway($query, $gateway)
    {
        return $query->where('setting', 'name')->where('gateway', $gateway);
    }
}
