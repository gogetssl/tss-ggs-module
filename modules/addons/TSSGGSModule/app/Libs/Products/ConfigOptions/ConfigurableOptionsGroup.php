<?php

namespace TSSGGSModule\App\Libs\Products\ConfigOptions;

use TSSGGSModule\App\Kernel\Lang;
use TSSGGSModule\App\Libs\Support\Edition;
use TSSGGSModule\App\Models\Whmcs\Product;
use TSSGGSModule\App\Models\Whmcs\ProductConfigGroup;
use TSSGGSModule\App\Models\Whmcs\ProductConfigLink;

/** The configurable options group of a WHMCS product: the one it has, or a new one named after the product */
class ConfigurableOptionsGroup
{
    public function getFirstOrCreateRelated(Product $product): ProductConfigGroup
    {
        $group = ProductConfigGroup::ofProductId($product->id)->first();
        if($group)
        {
            return $group;
        }

        $lang  = new Lang('admin');
        $group = ProductConfigGroup::create([
            'name'        => $lang->get('admin.products.config_group.name', ['productName' => $product->name]),
            'description' => $lang->get('admin.products.config_group.description', ['moduleName' => Edition::name()]),
        ]);
        if((int)$group->id > 0)
        {
            ProductConfigLink::create(['gid' => $group->id, 'pid' => $product->id]);
        }

        return $group;
    }
}
