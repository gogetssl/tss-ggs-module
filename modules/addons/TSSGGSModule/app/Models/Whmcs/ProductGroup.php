<?php

namespace TSSGGSModule\App\Models\Whmcs;

class ProductGroup extends \WHMCS\Product\Group
{
    public function products()
    {
        return $this->hasMany('TSSGGSModule\\App\\Models\\Whmcs\\Product', 'gid');
    }
}
