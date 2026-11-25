<?php

namespace ModulesGarden\TSSGGSModule\App\Models\Whmcs;

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

        return $this->belongsTo('ModulesGarden\\TSSGGSModule\\App\\Models\\Whmcs\\Hosting', 'relid');
    }

    public function service()
    {
        return $this->belongsTo('ModulesGarden\\TSSGGSModule\\App\\Models\\Whmcs\\Service', 'relid');
    }

    public function addon()
    {
        return $this->hasOne('ModulesGarden\\TSSGGSModule\\App\\Models\\Whmcs\\ServiceAddon', "id", "relid");
    }

    public function order()
    {
        return $this->belongsTo('ModulesGarden\\TSSGGSModule\\App\\Models\\Whmcs\\Order', 'orderid');
    }

    public function originalProduct()
    {
        return $this->hasOne('ModulesGarden\\TSSGGSModule\\App\\Models\\Whmcs\\Product', "id", "originalvalue");
    }

    public function newProduct()
    {
        return $this->hasOne('ModulesGarden\\TSSGGSModule\\App\\Models\\Whmcs\\Product', "id", "newvalue");
    }

    public function originalAddon()
    {
        return $this->hasOne('ModulesGarden\\TSSGGSModule\\App\\Models\\Whmcs\\Addon', "id", "originalvalue");
    }
    public function newAddon()
    {
        return $this->hasOne('ModulesGarden\\TSSGGSModule\\App\\Models\\Whmcs\\Addon', "id", "newvalue");
    }

    public function productFrom()
    {
        if ($this->type != 'package')
        {
            return new stdClass();
        }

        return $this->belongsTo('ModulesGarden\\TSSGGSModule\\App\\Models\\Whmcs\\Product', 'originalvalue');
    }
}
