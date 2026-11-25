<?php

namespace TSSGGSModule\App\Models\Whmcs;

class Product extends \WHMCS\Product\Product
{
    /**
     * @deprecated - use productGroup
     */
    public function group()
    {
        return $this->belongsTo('TSSGGSModule\\App\\Models\\Whmcs\\ProductGroup', 'gid');
    }

    public function productGroup()
    {
        return $this->belongsTo('TSSGGSModule\\App\\Models\\Whmcs\\ProductGroup', 'gid');
    }

    public function upgrades()
    {
        return $this->hasMany('TSSGGSModule\\App\\Models\\Whmcs\\ProductUpgrade', 'product_id');
    }

    public function upgradeProducts()
    {
        return $this->belongsToMany('TSSGGSModule\\App\\Models\\Whmcs\\Product', "tblproduct_upgrade_products", "product_id", "upgrade_product_id");
    }

    public function services()
    {
        return $this->hasMany('TSSGGSModule\\App\\Models\\Whmcs\\Service', "packageid");
    }

    public function customFields()
    {
        return $this->hasMany('TSSGGSModule\\App\\Models\\Whmcs\\CustomField', "relid")
            ->where("type", "=", "product")
            ->orderBy("sortorder");
    }
}
