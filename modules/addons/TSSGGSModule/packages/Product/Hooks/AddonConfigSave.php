<?php

// only through WHMCS: a direct request gets nothing (no output, no error with the server path)
if(!defined('WHMCS'))
{
    http_response_code(403);
    exit;
}


use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TSSGGSModule\Packages\Product\Services\ProductConfiguration;
use ModulesGarden\TSSGGSModule\Packages\Product\Helpers\RequiredCustomFieldsGenerator;
use Illuminate\Database\Capsule\Manager as DB;

$hookManager->register(
    function($params) {
        if (!\ModulesGarden\TSSGGSModule\Packages\Product\Helpers\ProductConfiguration::isSupportedInRequest())
        {
            return;
        }

        RequiredCustomFieldsGenerator::addRequiredAddonCustomFields($params['id']);

        if (empty(Request::get('customconfigoption', [])))
        {
            return;
        }

        try
        {
            DB::beginTransaction();

            (new ProductConfiguration($params['id'], ProductConfiguration::PRODUCT_ADDON_TYPE))
                ->flush()
                ->save(Request::get('customconfigoption'));

            DB::commit();

        } catch (\Exception $exception)
        {
            DB::rollback();
        }
    }, 1001);

