<?php

namespace TSSGGSModule\App\Models\Whmcs;

class ProductConfigOptionSub extends \WHMCS\Product\ConfigOptionSelection
{
    public function configOption()
    {
        return $this->belongsTo('TSSGGSModule\\App\\Models\\Whmcs\\ProductConfigOption', 'configid');
    }
}
