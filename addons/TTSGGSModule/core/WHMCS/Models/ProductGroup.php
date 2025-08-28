<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

class ProductGroup extends \WHMCS\Product\Group
{
    public function products()
    {
        return $this->hasMany("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Product", 'gid');
    }
}
