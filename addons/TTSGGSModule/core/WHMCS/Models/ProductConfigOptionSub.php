<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

class ProductConfigOptionSub extends \WHMCS\Product\ConfigOptionSelection
{
    public function configOption()
    {
        return $this->belongsTo("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\ProductConfigOption", 'configid');
    }
}
