<?php

namespace ModulesGarden\TSSGGSModule\Packages\Product\Database\Patches\Patches;

use ModulesGarden\TSSGGSModule\Packages\Product\Database\Patches\Source\PatchInterface;
use ModulesGarden\TSSGGSModule\Packages\Product\Models\ProductConfiguration;
use Illuminate\Database\Capsule\Manager as DB;

class TypeEnumColumnCreate implements PatchInterface
{
    public function execute(): void
    {
        $productConfigurationModel = new ProductConfiguration();
        $builder = $productConfigurationModel->getConnection()->getSchemaBuilder();

        if ($builder->hasColumn($productConfigurationModel->getTable(), 'type')) {
            return;
        }

        $statement = "ALTER TABLE {$productConfigurationModel->getTable()} ADD type ENUM('product', 'product_addon') DEFAULT 'product' AFTER product_id";
        DB::statement($statement);
    }
}
