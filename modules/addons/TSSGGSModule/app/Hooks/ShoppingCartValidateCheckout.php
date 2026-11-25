<?php

// only through WHMCS: a direct request gets nothing (no output, no error with the server path)
if(!defined('WHMCS'))
{
    http_response_code(403);
    exit;
}


use ModulesGarden\TSSGGSModule\App\Libs\Subscriptions\AcmeHelpers;
use WHMCS\Database\Capsule;

$hookManager->register(
    function($params) {
        $errors   = [];
        $products = isset($_SESSION['cart']['products']) ? $_SESSION['cart']['products'] : [];

        foreach ($products as $product)
        {
            $pid = isset($product['pid']) ? (int)$product['pid'] : 0;

            // code signing: 2 / 3 years only on an own HSM
            $csError = $pid ? \ModulesGarden\TSSGGSModule\App\Libs\ProductLines\CodeSigningService::cartError($pid, $product['billingcycle'] ?? '', (array)($product['configoptions'] ?? [])) : null;
            if ($csError)
            {
                $errors[] = \ModulesGarden\TSSGGSModule\App\Kernel\Lang::text($csError);
                continue;
            }

            if (!$pid || !AcmeHelpers::isAcmeWhmcsProduct($pid))
            {
                continue;
            }

            $sansOptionId = Capsule::table('tblproductconfigoptions')
                ->join('tblproductconfiglinks', 'tblproductconfiglinks.gid', '=', 'tblproductconfigoptions.gid')
                ->where('tblproductconfiglinks.pid', $pid)
                ->where(function($q) {
                    $q->where('tblproductconfigoptions.optionname', 'sans')
                      ->orWhere('tblproductconfigoptions.optionname', 'LIKE', 'sans|%');
                })
                ->value('tblproductconfigoptions.id');

            $wildcardOptionId = Capsule::table('tblproductconfigoptions')
                ->join('tblproductconfiglinks', 'tblproductconfiglinks.gid', '=', 'tblproductconfigoptions.gid')
                ->where('tblproductconfiglinks.pid', $pid)
                ->where(function($q) {
                    $q->where('tblproductconfigoptions.optionname', 'sans_wildcard')
                      ->orWhere('tblproductconfigoptions.optionname', 'LIKE', 'sans_wildcard|%');
                })
                ->value('tblproductconfigoptions.id');

            $configoptions = isset($product['configoptions']) ? $product['configoptions'] : [];
            $sansCount     = ($sansOptionId    && isset($configoptions[$sansOptionId]))    ? (int)$configoptions[$sansOptionId]    : 0;
            $wildcardCount = ($wildcardOptionId && isset($configoptions[$wildcardOptionId])) ? (int)$configoptions[$wildcardOptionId] : 0;

            if ($sansCount <= 0 && $wildcardCount <= 0)
            {
                $errors[] = \ModulesGarden\TSSGGSModule\App\Kernel\Lang::text('acme_order_requires_at_least_one_san');
            }
        }

        return $errors;
    },
    1
);
