<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

use stdClass;

class Upgrade extends \WHMCS\Service\Upgrade\Upgrade
{
    public function getNewBillingcycleAttribute()
    {
        $newvalue = explode(',', $this->newvalue);

        return $newvalue[1];
    }

    /**
     * @deprecated - use service
     */
    public function hosting()
    {
        if ($this->type != 'package')
        {
            return new stdClass();
        }

        return $this->belongsTo("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Hosting", 'relid');
    }

    public function service()
    {
        return $this->belongsTo("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Service", 'relid');
    }

    public function addon()
    {
        return $this->hasOne("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\ServiceAddon", "id", "relid");
    }

    public function order()
    {
        return $this->belongsTo("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Order", 'orderid');
    }

    public function originalProduct()
    {
        return $this->hasOne("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Product", "id", "originalvalue");
    }

    public function newProduct()
    {
        return $this->hasOne("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Product", "id", "newvalue");
    }

    public function originalAddon()
    {
        return $this->hasOne("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Addon", "id", "originalvalue");
    }
    public function newAddon()
    {
        return $this->hasOne("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Addon", "id", "newvalue");
    }

    public function productFrom()
    {
        if ($this->type != 'package')
        {
            return new stdClass();
        }

        return $this->belongsTo("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Product", 'originalvalue');
    }
}
