<?php

namespace ModulesGarden\TSSGGSModule\Core\WHMCS\Models;

class ProductGroup extends \WHMCS\Product\Group
{
    public function products()
    {
        return $this->hasMany("ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Product", 'gid');
    }
}
