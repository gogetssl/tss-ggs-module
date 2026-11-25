<?php

namespace ModulesGarden\TSSGGSModule\App\Models\Whmcs;

class ProductConfigOptionSub extends \WHMCS\Product\ConfigOptionSelection
{
    public function configOption()
    {
        return $this->belongsTo('ModulesGarden\\TSSGGSModule\\App\\Models\\Whmcs\\ProductConfigOption', 'configid');
    }
}
