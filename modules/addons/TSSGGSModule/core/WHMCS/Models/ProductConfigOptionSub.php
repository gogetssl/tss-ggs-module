<?php

namespace ModulesGarden\TSSGGSModule\Core\WHMCS\Models;

class ProductConfigOptionSub extends \WHMCS\Product\ConfigOptionSelection
{
    public function configOption()
    {
        return $this->belongsTo("ModulesGarden\TSSGGSModule\Core\WHMCS\Models\ProductConfigOption", 'configid');
    }
}
