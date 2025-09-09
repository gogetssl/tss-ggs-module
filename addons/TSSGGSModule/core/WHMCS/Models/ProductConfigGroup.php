<?php

namespace ModulesGarden\TSSGGSModule\Core\WHMCS\Models;

class ProductConfigGroup extends \WHMCS\Product\ConfigOptionGroup
{
    public function configOptions()
    {
        return $this->hasMany("ModulesGarden\TSSGGSModule\Core\WHMCS\Models\ProductConfigOption", "gid");
    }

    public function scopeOfProductId($query, $productId)
    {
        return $query->whereIn("id", ProductConfigLink::ofProductId($productId)->pluck("gid"));
    }
}