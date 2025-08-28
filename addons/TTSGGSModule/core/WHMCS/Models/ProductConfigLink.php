<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

class ProductConfigLink extends \WHMCS\Product\ConfigOptionGroupLinks
{
    public function configGroup()
    {
        return $this->hasOne(ProductConfigGroup::class, "id", "gid");
    }

    public function product()
    {
        return $this->hasOne(Product::class, "id", "pid");
    }

    public function scopeOfGroupId($query, $groupId)
    {
        return $query->where("gid", $groupId);
    }

    public function scopeOfProductId($query, $productId)
    {
        return $query->where("pid", $productId);
    }
}