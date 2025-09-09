<?php

use ModulesGarden\TSSGGSModule\Packages\ModuleSettings\Support\Facades\ModuleSettings;
use ModulesGarden\TSSGGSModule\Packages\Product\Services\ProductDuplicate;

$hookManager->register(
    function($params) {
        try
        {
            if (!$settings = ModuleSettings::get("duplicateProduct"))
            {
                return;
            }

            $duplicateService = new ProductDuplicate($params['pid']);
            $duplicateService->replicate(json_decode($settings, true));

            ModuleSettings::delete("duplicateProduct");
        }
        catch (\Exception $ex)
        {
        }
    }, 1001);

