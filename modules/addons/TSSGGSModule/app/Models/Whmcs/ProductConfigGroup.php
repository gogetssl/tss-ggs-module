<?php

namespace ModulesGarden\TSSGGSModule\App\Models\Whmcs;

class ProductConfigGroup extends \WHMCS\Product\ConfigOptionGroup
{
    public function configOptions()
    {
        return $this->hasMany('ModulesGarden\\TSSGGSModule\\App\\Models\\Whmcs\\ProductConfigOption', "gid");
    }

    public function scopeOfProductId($query, $productId)
    {
        return $query->whereIn("id", ProductConfigLink::ofProductId($productId)->pluck("gid"));
    }
}
