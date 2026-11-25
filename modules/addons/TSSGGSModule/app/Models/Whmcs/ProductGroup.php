<?php

namespace ModulesGarden\TSSGGSModule\App\Models\Whmcs;

class ProductGroup extends \WHMCS\Product\Group
{
    public function products()
    {
        return $this->hasMany('ModulesGarden\\TSSGGSModule\\App\\Models\\Whmcs\\Product', 'gid');
    }
}
